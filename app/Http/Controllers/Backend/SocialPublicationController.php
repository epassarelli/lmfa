<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\{AuditLog, PublicationTarget};
use App\Services\Publication\{MetaCredentialStore, SocialPublicationExecutor, SocialPublicationLedgerService, SocialPublicationRenderer, SocialPublicationSettings};
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Panel de publicaciones en redes: log, envío a demanda, horarios por red, pausa, ejecutor y token.
 * Spec: social-publication-laravel-executor (y social-publication-ledger-n8n para la conciliación).
 */
class SocialPublicationController extends Controller
{
    private function destinations(): array
    {
        return ['facebook' => config('social_publication.destination'), 'instagram' => config('social_publication.instagram_destination')];
    }

    public function index(Request $request, SocialPublicationSettings $settings, MetaCredentialStore $credentials, SocialPublicationExecutor $executor)
    {
        $kinds = array_keys(SocialPublicationSettings::KINDS);
        $filters = $request->validate(['content_kind' => ['nullable', Rule::in($kinds)], 'provider' => 'nullable|in:facebook,instagram',
            'status' => 'nullable|in:pending,reserved,processing,success,failed,uncertain,cancelled', 'from' => 'nullable|date', 'to' => 'nullable|date',
            'tab' => 'nullable|in:log,manual,config', 'm_kind' => ['nullable', Rule::in($kinds)], 'm_network' => 'nullable|in:facebook,instagram,both']);
        $tz = config('social_publication.timezone');
        $targets = PublicationTarget::with(['request.content', 'attempts' => fn ($q) => $q->select(['id', 'publication_target_id', 'attempt_number', 'status',
                'executor', 'error_code', 'response_payload_json', 'dispatch_started_at', 'finished_at', 'created_at'])->orderBy('attempt_number')])
            ->where('executor', 'n8n')->whereIn('destination_key', array_values($this->destinations()))
            ->when($filters['content_kind'] ?? null, fn ($q, $v) => $q->where('ledger_content_kind', $v))
            ->when($filters['provider'] ?? null, fn ($q, $v) => $q->where('provider', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->where('updated_at', '>=', \Carbon\Carbon::parse($v, $tz)->startOfDay()->utc()))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->where('updated_at', '<=', \Carbon\Carbon::parse($v, $tz)->endOfDay()->utc()))
            ->orderByDesc('updated_at')->orderByDesc('id')->paginate(20)->withQueryString();
        $runs = AuditLog::where('entity_type', 'social_publication_run')->latest('id')->limit(15)->get();

        $mKind = $filters['m_kind'] ?? 'news';
        $mNetwork = $filters['m_network'] ?? 'both';
        $mProvider = $mNetwork === 'instagram' ? 'instagram' : 'facebook';
        $queue = $executor->queue($mKind, $mProvider)
            ->select(['news' => ['news.id', 'news.title', 'news.published_at'], 'events' => ['events.id', 'events.title', 'events.start_at'],
                'albums' => ['albunes.id', 'albunes.album', 'albunes.anio']][$mKind])
            ->limit(50)->get();
        $lastSent = PublicationTarget::where('executor', 'n8n')->where('ledger_content_kind', $mKind)
            ->where('destination_key', $this->destinations()[$mProvider])
            ->whereIn('status', ['success', 'published'])->with('request.content')->latest('published_at')->first();

        return view('backend.social-publications.index', [
            'targets' => $targets, 'runs' => $runs, 'settings' => $settings->all(), 'tokens' => $credentials->status(),
            'blocked' => $executor->blockedReason(), 'tab' => $filters['tab'] ?? 'log', 'queue' => $queue, 'lastSent' => $lastSent,
            'mKind' => $mKind, 'mNetwork' => $mNetwork, 'tz' => $tz, 'preview' => session('social_preview'),
        ]);
    }

    public function updateSettings(Request $request, SocialPublicationSettings $settings)
    {
        $rules = ['executor' => 'required|in:n8n,laravel', 'paused' => 'nullable|boolean', 'alert_email' => 'nullable|email|max:190'];
        foreach (array_keys(SocialPublicationSettings::KINDS) as $kind) {
            foreach (array_keys(SocialPublicationSettings::PROVIDERS) as $provider) {
                $rules["schedules.$kind.$provider.enabled"] = 'nullable|boolean';
                // Un tipo ausente del formulario conserva su horario guardado (p. ej., un tipo recién sumado).
                $rules["schedules.$kind.$provider.time"] = ["required_with:schedules.$kind", 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'];
                $rules["schedules.$kind.$provider.days"] = 'nullable|array';
                $rules["schedules.$kind.$provider.days.*"] = 'integer|between:1,7';
            }
        }
        $data = $request->validate($rules, ['schedules.*.*.time.regex' => 'El horario debe tener formato HH:MM.']);
        $old = $settings->all();
        $schedules = [];
        foreach ($data['schedules'] ?? [] as $kind => $providers) {
            foreach ($providers as $provider => $slot) {
                $enabled = (bool) ($slot['enabled'] ?? false);
                $days = array_map('intval', $slot['days'] ?? []);
                if ($enabled && !$days) {
                    return back()->withInput()->withErrors(['schedules' => 'Un horario activo necesita al menos un día ('.SocialPublicationSettings::KINDS[$kind].' en '.SocialPublicationSettings::PROVIDERS[$provider].').']);
                }
                $schedules[$kind][$provider] = ['enabled' => $enabled, 'time' => $slot['time'], 'days' => $days ?: SocialPublicationSettings::ALL_DAYS];
            }
        }
        $new = ['executor' => $data['executor'], 'paused' => $request->boolean('paused'), 'alert_email' => $data['alert_email'] ?? null, 'schedules' => $schedules];
        $settings->update($new);
        AuditLog::log('social_publication_settings', 0, 'updated', array_intersect_key($old, $new), $new);
        return redirect()->route('social-publications.index', ['tab' => 'config'])->with('success', 'Configuración guardada.');
    }

    public function storeToken(Request $request, MetaCredentialStore $credentials, SocialPublicationExecutor $executor)
    {
        $data = $request->validate(['provider' => 'required|in:facebook,instagram', 'token' => 'required|string|min:40|max:1024']);
        $credentials->put($data['provider'], $data['token'], $request->user()->id);
        AuditLog::log('social_publication_token', 0, 'stored', [], ['provider' => $data['provider']]);
        $check = $executor->checkTokens(false)[$data['provider']] ?? null;
        $ok = $check && $check['ok'];
        return redirect()->route('social-publications.index', ['tab' => 'config'])->with($ok ? 'success' : 'warning',
            $ok ? 'Token guardado y verificado ('.$check['name'].').' : 'Token guardado, pero Meta no lo validó: '.($check['error'] ?? 'sin respuesta').'.');
    }

    public function checkToken(SocialPublicationExecutor $executor)
    {
        $results = $executor->checkTokens(false);
        $text = collect($results)->map(fn ($r, $p) => ucfirst($p).': '.($r['ok'] ? 'OK ('.$r['name'].')' : 'error — '.$r['error']))->implode(' · ');
        $ok = $results && collect($results)->every(fn ($r) => $r['ok']);
        return redirect()->route('social-publications.index', ['tab' => 'config'])->with($ok ? 'success' : 'warning', $text ?: 'No hay tokens cargados.');
    }

    public function forgetToken(string $provider, MetaCredentialStore $credentials)
    {
        abort_unless(in_array($provider, MetaCredentialStore::PROVIDERS, true), 404);
        $credentials->forget($provider);
        AuditLog::log('social_publication_token', 0, 'removed', [], ['provider' => $provider]);
        return redirect()->route('social-publications.index', ['tab' => 'config'])->with('success', 'Token eliminado.');
    }

    public function preview(Request $request, SocialPublicationExecutor $executor)
    {
        $data = $request->validate(['content_kind' => ['required', Rule::in(array_keys(SocialPublicationSettings::KINDS))], 'network' => 'required|in:facebook,instagram,both', 'content_id' => 'required|integer|min:1']);
        try {
            $snapshot = $executor->preview($data['content_kind'], $data['network'] === 'instagram' ? 'instagram' : 'facebook', (int) $data['content_id']);
            $asset = app(SocialPublicationRenderer::class)->render($snapshot, 'png');
        } catch (HttpExceptionInterface|\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->manual($data)->withErrors(['preview' => 'No se pudo generar la vista previa: '.($e->getMessage() ?: 'el contenido ya no está en la cola.')]);
        }
        return $this->manual($data)->with('social_preview', ['content_id' => (int) $data['content_id'], 'title' => $snapshot['title'],
            'copy' => $snapshot['copy'], 'url' => $snapshot['url'], 'hash' => $asset['asset_sha256']]);
    }

    public function previewAsset(string $hash)
    {
        $path = 'social-publications/assets/'.$hash.'.png';
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->file(Storage::disk('local')->path($path), ['Content-Type' => 'image/png', 'Cache-Control' => 'private, max-age=600']);
    }

    public function publish(Request $request, SocialPublicationExecutor $executor)
    {
        $data = $request->validate(['content_kind' => ['required', Rule::in(array_keys(SocialPublicationSettings::KINDS))], 'network' => 'required|in:facebook,instagram,both',
            'content_id' => 'required|integer|min:1', 'confirmed' => 'accepted']);
        @set_time_limit(180);
        $lines = []; $ok = true;
        foreach ($data['network'] === 'both' ? ['facebook', 'instagram'] : [$data['network']] as $provider) {
            $result = $executor->publish($data['content_kind'], $provider, (int) $data['content_id'], $request->user(), 'manual');
            $lines[] = ucfirst($provider).': '.$result['message'].(!empty($result['external_url']) ? ' '.$result['external_url'] : '');
            // Instagram necesita el éxito de Facebook: si Facebook no salió, no se intenta.
            if ($result['status'] !== 'success') { $ok = false; break; }
        }
        return $this->manual($data)->with($ok ? 'success' : 'warning', implode(' · ', $lines));
    }

    private function manual(array $data)
    {
        return redirect()->route('social-publications.index', ['tab' => 'manual', 'm_kind' => $data['content_kind'], 'm_network' => $data['network']]);
    }

    public function eligibility(Request $request, SocialPublicationLedgerService $ledger)
    {
        $data = $request->validate(['content_kind' => 'required|in:news,events', 'content_id' => 'required|integer|min:1', 'enabled' => 'required|boolean', 'portal_only_override' => 'nullable|accepted']);
        $ledger->enable($data['content_kind'], $data['content_id'], (bool) $data['enabled'], $request->boolean('portal_only_override'), $request->user()->id);
        return back()->with('success', 'Habilitación actualizada. No se envió ninguna publicación.');
    }

    public function resolve(Request $request, int $target)
    {
        $data = $request->validate(['action' => 'required|in:retry,published,not_published', 'reason' => 'required|string|min:10|max:500', 'confirmed' => 'required|accepted',
            'external_post_id' => 'required_if:action,published|nullable|string|max:128', 'external_url' => 'nullable|string|max:512']);
        DB::transaction(function () use ($target, $data) {
            $t = PublicationTarget::where('executor', 'n8n')->whereIn('destination_key', array_values($this->destinations()))->lockForUpdate()->findOrFail($target);
            $old = $t->status;
            if ($data['action'] === 'retry') {
                abort_unless($old === 'failed' && $t->attempts_count < 3 && (!$t->retry_after || $t->retry_after->isPast()), 409, 'Este destino no admite reintento ahora.');
                $t->update(['status' => 'pending']);
            } else {
                abort_unless($old === 'uncertain', 409);
                if ($data['action'] === 'published') {
                    if ($t->provider === 'instagram') {
                        abort_unless(preg_match('/^[0-9]{5,32}$/D', $data['external_post_id']), 422, 'ID de Instagram inválido.');
                        abort_unless(preg_match('~^https://www\.instagram\.com/(?:p|reel)/[A-Za-z0-9_-]+/$~D', $data['external_url'] ?? ''), 422, 'Falta el permalink de Instagram.');
                        $url = $data['external_url'];
                    } else {
                        $page = substr($t->destination_key, strlen('facebook:page:'));
                        abort_unless(preg_match('/^'.preg_quote($page, '/').'_[0-9]+$/D', $data['external_post_id']), 422, 'ID de publicación no corresponde a la Página.');
                        $url = 'https://www.facebook.com/'.$data['external_post_id'];
                    }
                    $t->update(['status' => 'success', 'external_post_id' => $data['external_post_id'], 'external_url' => $url, 'published_at' => now()]);
                } else {
                    $t->update(['status' => 'failed', 'retry_after' => now()->addMinutes(30)]);
                }
                $t->attempts()->where('status', 'uncertain')->update(['status' => $t->status, 'finished_at' => now(),
                    'external_post_id' => $t->external_post_id, 'external_url' => $t->external_url]);
                $t->update(['lease_token_hash' => null]);
            }
            AuditLog::log('publication_target', $t->id, 'manual_resolution', ['status' => $old], ['status' => $t->status, 'reason' => $data['reason']]);
            (new SocialPublicationLedgerService($t->provider))->aggregate($t);
        });
        return back()->with('success', 'Acción registrada con auditoría.');
    }
}
