<?php

namespace App\Services\Publication;

use App\Models\{AuditLog, Event, News, PublicationAttempt, PublicationRequest, PublicationTarget, SocialPublicationEligibility};
use Illuminate\Support\Facades\DB;

class SocialPublicationLedgerService
{
    public function identity(string $kind, int $id): array
    {
        abort_unless(in_array($kind, ['news', 'events'], true), 422, 'Tipo no permitido.');
        return ['content_kind' => $kind, 'content_id' => $id, 'provider' => 'facebook', 'destination_key' => config('social_publication.destination')];
    }

    public function snapshot(SocialPublicationEligibility $e, ?\Illuminate\Database\Eloquent\Model $loaded = null): array
    {
        $class = $e->content_kind === 'news' ? News::class : Event::class;
        $content = $loaded ?? $class::lockForUpdate()->findOrFail($e->content_id);
        abort_unless($e->enabled && $content->isPublished(), 409, 'Contenido no habilitado o no público.');
        abort_if($content->publication_mode === 'portal_only' && !$e->portal_only_override, 409, 'Falta autorización portal_only.');
        if ($e->content_kind === 'events') {
            abort_unless(($content->end_at ?? $content->start_at)?->gte(now()), 409, 'Evento vencido o sin fecha.');
        }
        abort_unless(filled($content->slug) && filled($content->title), 409, 'Falta título o URL.');
        $url = $e->content_kind === 'news' ? route('noticias.show', $content->slug) : route('cartelera.show', ['provinceOrSlug' => $content->slug]);
        $title = trim(strip_tags($content->title));
        $excerpt = trim(strip_tags($content->excerpt ?? ''));
        $approved = ['news' => 'mfa-noticias-v4', 'events' => 'mfa-agenda-v4'];
        abort_unless(config('social_publication.templates.'.$e->content_kind) === $approved[$e->content_kind]
            && config('social_publication.catalog_version') === '4.0.0', 409, 'Plantilla no aprobada para este tipo.');
        $payload = ['content_kind' => $e->content_kind, 'content_id' => $e->content_id, 'title' => $title, 'excerpt' => $excerpt,
            'url' => $url, 'copy' => $title.($excerpt ? "\n\n".$excerpt : '')."\n\n".$url,
            'template_id' => config('social_publication.templates.'.$e->content_kind), 'template_catalog_version' => config('social_publication.catalog_version')];
        if ($e->content_kind === 'events') {
            $payload += ['event_start_at' => $content->start_at?->toIso8601String(), 'event_end_at' => $content->end_at?->toIso8601String(),
                'event_timezone' => $content->timezone ?: 'America/Argentina/Buenos_Aires', 'event_city' => trim(strip_tags($content->city ?? ''))];
        }
        $payload['content_hash'] = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        return $payload;
    }

    public function enable(string $kind, int $id, bool $enabled, bool $override, int $actor): PublicationTarget
    {
        return DB::transaction(function () use ($kind, $id, $enabled, $override, $actor) {
            $this->identity($kind, $id);
            ($kind === 'news' ? News::class : Event::class)::findOrFail($id);
            $e = SocialPublicationEligibility::firstOrCreate($this->identity($kind, $id));
            $e = SocialPublicationEligibility::lockForUpdate()->findOrFail($e->id);
            $old = $e->only(['enabled', 'portal_only_override']);
            $e->fill(['enabled' => $enabled, 'portal_only_override' => $override,
                $enabled ? 'enabled_by' : 'disabled_by' => $actor, $enabled ? 'enabled_at' : 'disabled_at' => now()])->save();
            if ($enabled) {
                $this->snapshot($e);
                abort_if(app(SocialPublicationGuard::class)->legacyExists($kind, $id), 409, 'Revisar historial legacy antes de habilitar.');
            }
            $target = PublicationTarget::where('ledger_content_kind', $kind)->where('ledger_content_id', $id)->where('provider', 'facebook')->where('destination_key', $e->destination_key)->lockForUpdate()->first();
            if (!$target) {
                $request = PublicationRequest::create(['content_type' => $kind === 'news' ? News::class : Event::class, 'content_id' => $id,
                    'requested_by' => $actor, 'mode' => 'social_ledger', 'status' => 'pending']);
                $target = $request->targets()->create(['ledger_content_kind' => $kind, 'ledger_content_id' => $id, 'provider' => 'facebook',
                    'destination_key' => $e->destination_key, 'executor' => 'n8n', 'status' => $enabled ? 'pending' : 'cancelled']);
            } elseif (!$enabled && in_array($target->status, ['pending', 'reserved', 'failed'])) {
                $target->attempts()->where('status', 'reserved')->update(['status' => 'cancelled', 'finished_at' => now()]);
                $target->update(['status' => 'cancelled', 'lease_token_hash' => null, 'lease_expires_at' => null]);
            } elseif ($enabled && $target->status === 'cancelled') {
                $target->update(['status' => 'pending']);
            }
            AuditLog::log('social_publication_eligibility', $e->id, $enabled ? 'enabled' : 'disabled', $old, $e->only(['enabled', 'portal_only_override']));
            return $target;
        });
    }

    public function claim(string $kind, int $id, string $key, ?string $execution): array
    {
        return DB::transaction(function () use ($kind, $id, $key, $execution) {
            $e = SocialPublicationEligibility::where($this->identity($kind, $id))->lockForUpdate()->firstOrFail();
            $target = PublicationTarget::where('ledger_content_kind', $kind)->where('ledger_content_id', $id)->where('provider', 'facebook')->where('destination_key', $e->destination_key)->lockForUpdate()->firstOrFail();
            $replay = PublicationAttempt::where('request_key', $key)->first();
            if ($replay) {
                abort_unless($replay->publication_target_id === $target->id, 409, 'Clave usada para otro contenido.');
                return ['target_id' => $target->id, 'attempt_id' => $replay->id, 'replayed' => true];
            }
            $this->expireLocked($target);
            abort_unless(in_array($target->status, ['pending', 'failed']) && $target->attempts_count < 3 && (!$target->retry_after || $target->retry_after->isPast()), 409, 'Destino no reservable.');
            abort_if(app(SocialPublicationGuard::class)->legacyExists($kind, $id), 409, 'Existe historial legacy.');
            $payload = $this->snapshot($e);
            $lease = bin2hex(random_bytes(32));
            $expires = now()->addSeconds(config('social_publication.lease_seconds'));
            $attempt = $target->attempts()->create(['attempt_number' => ((int) $target->attempts()->max('attempt_number')) + 1,
                'status' => 'reserved', 'executor' => 'n8n', 'request_key' => $key, 'n8n_execution_id' => $execution, 'lease_expires_at' => $expires]);
            $target->update(['status' => 'reserved', 'lease_token_hash' => hash('sha256', $lease), 'lease_expires_at' => $expires,
                'payload_snapshot_json' => $payload, 'content_hash' => $payload['content_hash'], 'template_id' => $payload['template_id'], 'template_catalog_version' => $payload['template_catalog_version'], 'asset_reference' => null, 'asset_sha256' => null]);
            return ['target_id' => $target->id, 'attempt_id' => $attempt->id, 'lease' => $lease, 'lease_expires_at' => $expires->toIso8601String(), 'payload' => $payload, 'replayed' => false];
        });
    }

    private function locked(int $id, array $data): array
    {
        $identity = PublicationTarget::where('executor', 'n8n')->where('destination_key', config('social_publication.destination'))->findOrFail($id);
        SocialPublicationEligibility::where($this->identity($identity->ledger_content_kind, $identity->ledger_content_id))->lockForUpdate()->firstOrFail();
        $target = PublicationTarget::where('executor', 'n8n')->where('destination_key', config('social_publication.destination'))->lockForUpdate()->findOrFail($id);
        $attempt = $target->attempts()->lockForUpdate()->findOrFail($data['attempt_id']);
        abort_unless($target->lease_token_hash && hash_equals($target->lease_token_hash, hash('sha256', $data['lease'])), 409, 'Reserva inválida.');
        abort_unless($attempt->id === $target->attempts()->max('id'), 409, 'Intento reemplazado.');
        return [$target, $attempt];
    }

    public function begin(int $id, array $data): array
    {
        return DB::transaction(function () use ($id, $data) {
            [$target, $attempt] = $this->locked($id, $data);
            abort_unless($target->status === 'reserved' && $attempt->status === 'reserved' && $target->lease_expires_at->isFuture(), 409, 'No iniciar otro envío.');
            $e = SocialPublicationEligibility::where($this->identity($target->ledger_content_kind, $target->ledger_content_id))->firstOrFail();
            $snapshot = $this->snapshot($e);
            abort_unless(hash_equals($target->content_hash, $data['content_hash']) && hash_equals($target->content_hash, $snapshot['content_hash']), 409, 'Contenido modificado: esperar vencimiento y reservar de nuevo.');
            if (str_starts_with($data['asset_reference'], 'sha256:')) {
                $hash = $data['asset_sha256'];
                abort_unless($data['asset_reference'] === 'sha256:'.$hash && preg_match('/^[a-f0-9]{64}$/D', $hash), 422, 'Huella inválida.');
                $path = \Illuminate\Support\Facades\Storage::disk('local')->path('social-publications/assets/'.$hash.'.png');
                abort_unless(is_file($path) && hash_equals($hash, hash_file('sha256', $path)), 422, 'Placa ausente o alterada.');
                $rendered = $attempt->request_payload_json ?? [];
                abort_unless(($rendered['asset_sha256'] ?? null) === $hash, 409, 'La placa no corresponde al render de este intento.');
            } else {
            $url = parse_url($data['asset_reference']);
            abort_unless(($url['scheme'] ?? '') === 'https' && in_array($url['host'] ?? '', config('social_publication.asset_hosts'), true) && (!isset($url['port']) || $url['port'] === 443) && empty($url['query']) && empty($url['fragment']), 422, 'Asset no permitido.');
            abort_if(isset($url['user']) || isset($url['pass']), 422);
            }
            $target->update(['status' => 'processing', 'attempts_count' => $target->attempts_count + 1, 'asset_reference' => $data['asset_reference'], 'asset_sha256' => $data['asset_sha256']]);
            $attempt->update(['status' => 'processing', 'dispatch_started_at' => now(),
                'request_payload_json' => $target->payload_snapshot_json + ['asset_reference' => $data['asset_reference'], 'asset_sha256' => $data['asset_sha256']]]);
            $this->aggregate($target);
            return ['target_id' => $id, 'attempt_id' => $attempt->id, 'may_send' => true];
        });
    }

    public function result(int $id, array $data): array
    {
        return DB::transaction(function () use ($id, $data) {
            [$target, $attempt] = $this->locked($id, $data);
            abort_unless($attempt->dispatch_started_at, 409, 'Envío no iniciado.');
            $outcome = $data['outcome'];
            $post = $data['external_post_id'] ?? null;
            if ($outcome === 'success') {
                $page = substr($target->destination_key, strlen('facebook:page:'));
                abort_unless(is_string($post) && preg_match('/^'.preg_quote($page, '/').'_[0-9]+$/D', $post), 422, 'ID de publicación no corresponde a la Página.');
            }
            $fingerprint = hash('sha256', json_encode([$outcome, $post, $data['error_code'] ?? null]));
            if ($attempt->outcome_fingerprint === $fingerprint) {
                return $this->status($target);
            }
            abort_if($attempt->outcome_fingerprint && !($attempt->status === 'uncertain' && $outcome === 'success'), 409, 'Resultado contradictorio.');
            abort_if(in_array($target->status, ['success', 'published']) || !in_array($target->status, ['processing', 'uncertain']), 409, 'Resultado cerrado.');
            $state = ['success' => 'success', 'definitive_failure' => 'failed', 'uncertain' => 'uncertain'][$outcome];
            $target->update(['status' => $state, 'external_post_id' => $outcome === 'success' ? $post : null,
                'external_url' => $outcome === 'success' ? 'https://www.facebook.com/'.$post : null, 'published_at' => $outcome === 'success' ? now() : null,
                'retry_after' => $state === 'failed' ? now()->addSeconds([60, 300, 1800][min(2, $target->attempts_count - 1)]) : null,
                'last_error_code' => $data['error_code'] ?? null, 'last_error_message' => $state === 'success' ? null : 'Revisar el intento y conciliar antes de reenviar.']);
            $attempt->update(['status' => $state, 'finished_at' => now(), 'external_post_id' => $target->external_post_id, 'external_url' => $target->external_url,
                'outcome_fingerprint' => $fingerprint, 'is_retryable' => $state === 'failed' && $target->attempts_count < 3, 'error_code' => $target->last_error_code]);
            $this->aggregate($target);
            return $this->status($target);
        });
    }

    public function render(int $id, array $data): array
    {
        // Render outside the DB transaction; revalidate the reservation before recording it.
        $snapshot = DB::transaction(function () use ($id, $data) {
            [$target, $attempt] = $this->locked($id, $data);
            abort_unless($target->status === 'reserved' && $attempt->status === 'reserved' && $target->lease_expires_at->isFuture(), 409);
            return $target->payload_snapshot_json;
        });
        $asset = app(SocialPublicationRenderer::class)->render($snapshot);
        DB::transaction(function () use ($id, $data, $asset, $snapshot) {
            [$target, $attempt] = $this->locked($id, $data);
            abort_unless($target->status === 'reserved' && $target->lease_expires_at->isFuture(), 409);
            $attempt->update(['request_payload_json' => $snapshot + ['asset_sha256' => $asset['asset_sha256'], 'asset_reference' => $asset['asset_reference']]]);
        });
        return $asset;
    }

    public function expireLocked(PublicationTarget $target): void
    {
        if (!in_array($target->status, ['reserved', 'processing']) || !$target->lease_expires_at?->isPast()) { return; }
        $state = $target->status === 'reserved' ? 'pending' : 'uncertain';
        $target->attempts()->whereIn('status', ['reserved', 'processing'])->update(['status' => $state === 'pending' ? 'cancelled' : 'uncertain', 'finished_at' => now()]);
        $target->update(['status' => $state]);
        $this->aggregate($target);
    }

    public function aggregate(PublicationTarget $target): void
    {
        $statuses = $target->request->targets()->pluck('status');
        $status = $statuses->every(fn ($s) => in_array($s, ['success', 'published'])) ? 'completed'
            : ($statuses->intersect(['processing', 'uncertain', 'reserved'])->isNotEmpty() ? 'processing' : ($statuses->contains('failed') ? 'failed' : 'pending'));
        $target->request->update(['status' => $status]);
    }

    public function status(PublicationTarget $target): array
    {
        return $target->only(['id', 'ledger_content_kind', 'ledger_content_id', 'destination_key', 'status', 'published_at', 'external_post_id', 'external_url', 'template_id', 'template_catalog_version', 'content_hash', 'attempts_count', 'retry_after', 'last_error_code', 'last_error_message']);
    }
}
