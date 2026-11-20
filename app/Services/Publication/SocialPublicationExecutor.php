<?php

namespace App\Services\Publication;

use App\Mail\SocialPublicationAlertMail;
use App\Models\{AuditLog, PublicationAttempt, PublicationTarget, User};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\{Auth, Cache, Log, Mail};
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Ejecutor Laravel de publicaciones en redes (reemplaza la orquestación de n8n).
 * Recorre los mismos pasos del ledger que n8n (claim → render → begin → Meta → result) y clasifica los resultados igual,
 * por lo que conserva todas las protecciones contra duplicados. Spec: social-publication-laravel-executor.
 */
class SocialPublicationExecutor
{
    public function __construct(
        protected SocialPublicationSettings $settings,
        protected MetaCredentialStore $credentials,
        protected MetaGraphClient $meta,
    ) {}

    public function queue(string $kind, string $provider): Builder
    {
        abort_unless(isset(SocialPublicationSettings::KINDS[$kind], SocialPublicationSettings::PROVIDERS[$provider]), 422);
        return app($kind === 'news' ? AutomaticNewsPublication::class : AutomaticEventPublication::class)->candidates($provider);
    }

    /** Snapshot (texto, URL, plantilla) del contenido en la cola, sin reservar nada. */
    public function preview(string $kind, string $provider, int $contentId): array
    {
        $queue = app($kind === 'news' ? AutomaticNewsPublication::class : AutomaticEventPublication::class);
        $content = $this->queue($kind, $provider)->whereKey($contentId)->firstOrFail();
        return (new SocialPublicationLedgerService($provider))->snapshot($queue->eligibility($content, $provider), $content);
    }

    /** Horarios vencidos: cada uno se marca como corrido antes de publicar, para no repetirse aunque la corrida falle. */
    public function runDue(): array
    {
        if (!$this->ready()) { return []; }
        $results = [];
        foreach ($this->settings->dueSlots() as $slot) {
            $this->settings->markRun($slot['kind'], $slot['provider']);
            $results[] = $this->publish($slot['kind'], $slot['provider'], null, null, 'schedule');
        }
        return $results;
    }

    public function blockedReason(): ?string
    {
        if (!config('social_publication.enabled')) { return 'La automatización está deshabilitada en el servidor (SOCIAL_PUBLICATION_ENABLED).'; }
        if (!$this->settings->executorIsLaravel()) { return 'El ejecutor activo es n8n: Laravel no publica.'; }
        if ($this->settings->paused()) { return 'Las publicaciones están en pausa.'; }
        return null;
    }

    public function ready(): bool
    {
        return $this->blockedReason() === null;
    }

    public function publish(string $kind, string $provider, ?int $contentId, ?User $actor, string $trigger): array
    {
        $base = ['kind' => $kind, 'provider' => $provider, 'trigger' => $trigger, 'content_id' => $contentId];
        if ($reason = $this->blockedReason()) { return $base + ['status' => 'skipped', 'message' => $reason]; }
        if ($provider === 'instagram' && !config('social_publication.instagram_enabled')) {
            return $this->finish($base + ['status' => 'error', 'message' => 'Instagram está deshabilitado en el servidor (SOCIAL_PUBLICATION_INSTAGRAM_ENABLED).']);
        }
        $lock = Cache::lock('social-publications:executor', 300);
        if (!$lock->get()) { return $base + ['status' => 'skipped', 'message' => 'Hay otra publicación en curso. Probá de nuevo en unos minutos.']; }
        try {
            $actor ??= User::find((int) (config('social_publication.operators')[0] ?? 0));
            if (!$actor) { return $this->finish($base + ['status' => 'error', 'message' => 'Falta el operador técnico (SOCIAL_PUBLICATION_OPERATOR_IDS).']); }
            if (Auth::id() !== $actor->id) { Auth::setUser($actor); }
            $token = $this->credentials->token($provider);
            if (!$token) { return $this->finish($base + ['status' => 'error', 'message' => 'Falta cargar el token de Meta para '.SocialPublicationSettings::PROVIDERS[$provider].'.']); }
            return $this->finish($this->dispatch($base, $kind, $provider, $contentId, $token));
        } finally {
            $lock->release();
        }
    }

    private function dispatch(array $base, string $kind, string $provider, ?int $contentId, string $token): array
    {
        $query = $this->queue($kind, $provider);
        $content = $contentId ? $query->whereKey($contentId)->first() : $query->first();
        if (!$content) {
            return $base + ($contentId ? ['status' => 'error', 'message' => 'El contenido elegido ya no está en la cola de esa red.']
                : ['status' => 'empty', 'message' => 'No hay contenidos pendientes en la cola.']);
        }
        $base['content_id'] = $content->id;
        $base['title'] = $content->title;
        $ledger = new SocialPublicationLedgerService($provider);

        // Antes de begin no hubo envío: ante cualquier error la reserva vence sola y el contenido vuelve a la cola.
        try {
            $claim = $ledger->claim($kind, $content->id, (string) Str::uuid(), 'portal-'.$base['trigger'].'-'.now()->format('YmdHis'), 'portal');
            abort_if($claim['replayed'] || empty($claim['lease']), 409, 'Reserva repetida: no se envía.');
            $base['target_id'] = $claim['target_id'];
            $lease = ['attempt_id' => $claim['attempt_id'], 'lease' => $claim['lease']];
            $asset = $ledger->render($claim['target_id'], $lease);
            $begin = $ledger->begin($claim['target_id'], $lease + ['content_hash' => $claim['payload']['content_hash'],
                'asset_reference' => $asset['asset_reference'], 'asset_sha256' => $asset['asset_sha256']]);
            abort_unless($begin['may_send'] ?? false, 409, 'El portal no autorizó el envío.');
        } catch (HttpExceptionInterface|QueryException $e) {
            return $base + ['status' => 'error', 'message' => 'No se envió nada: '.($e instanceof HttpExceptionInterface ? $e->getMessage() : 'conflicto de reserva').'.'];
        } catch (\Throwable $e) {
            Log::error('[social-publications] Error antes de enviar', ['exception' => class_basename($e), 'target' => $base['target_id'] ?? null]);
            return $base + ['status' => 'error', 'message' => 'No se envió nada: error interno ('.class_basename($e).').'];
        }

        // Desde acá Meta pudo haber recibido el pedido: ningún error permite reenviar sin conciliación.
        try {
            $outcome = $provider === 'facebook'
                ? $this->sendFacebook($token, $claim, $asset)
                : $this->sendInstagram($ledger, $token, $claim, $lease, $asset);
        } catch (\Throwable $e) {
            $outcome = ['outcome' => 'uncertain', 'error_code' => 'EXECUTOR_EXCEPTION', 'evidence' => ['exception' => class_basename($e)]];
        }
        $evidence = $outcome['evidence'] ?? [];
        unset($outcome['evidence']);
        try {
            $status = $ledger->result($claim['target_id'], $lease + $outcome);
        } catch (\Throwable $e) {
            Log::error('[social-publications] No se pudo registrar el resultado', ['exception' => class_basename($e), 'target' => $claim['target_id']]);
            return $base + ['status' => 'uncertain', 'message' => 'Se intentó publicar pero no se pudo registrar el resultado: verificar en la red y conciliar.'];
        }
        if ($evidence) {
            PublicationAttempt::find($claim['attempt_id'])?->update(['response_payload_json' => $evidence]);
        }
        $messages = ['success' => 'Publicado.', 'failed' => 'Meta rechazó la publicación (no se publicó). Queda para reintento.',
            'uncertain' => 'Resultado incierto: verificar en la red y conciliar desde el log antes de reintentar.'];
        return $base + ['status' => $status['status'], 'message' => $messages[$status['status']] ?? $status['status'],
            'external_url' => $status['external_url'] ?? null, 'error_code' => $status['last_error_code'] ?? null];
    }

    private function sendFacebook(string $token, array $claim, array $asset): array
    {
        $page = substr(config('social_publication.destination'), strlen('facebook:page:'));
        $response = $this->meta->facebookPhoto($token, $page, base64_decode($asset['asset_base64'], true) ?: '', 'mfa-'.$claim['target_id'].'.png', $claim['payload']['copy']);
        $post = $response['body']['post_id'] ?? null;
        if ($response['status'] >= 200 && $response['status'] < 300 && is_string($post) && preg_match('/^'.preg_quote($page, '/').'_[0-9]+$/D', $post)) {
            return ['outcome' => 'success', 'external_post_id' => $post];
        }
        if ($this->rejected($response)) {
            return ['outcome' => 'definitive_failure', 'error_code' => 'META_REJECTED', 'evidence' => $this->evidence($response)];
        }
        return ['outcome' => 'uncertain', 'error_code' => 'META_OUTCOME_UNKNOWN', 'evidence' => $this->evidence($response)];
    }

    private function sendInstagram(SocialPublicationLedgerService $ledger, string $token, array $claim, array $lease, array $asset): array
    {
        $account = substr(config('social_publication.instagram_destination'), strlen('instagram:account:'));
        $container = $this->meta->instagramContainer($token, $account, $asset['asset_url'], $claim['payload']['copy']);
        $containerId = $container['body']['id'] ?? null;
        // Sin contenedor no puede haber publicación: es un fallo definitivo y seguro de reintentar.
        if ($container['status'] !== 200 || !is_string($containerId) || !preg_match('/^[0-9]{5,32}$/D', $containerId)) {
            return ['outcome' => 'definitive_failure', 'error_code' => $this->rejected($container) ? 'IG_CONTAINER_REJECTED' : 'IG_CONTAINER_UNAVAILABLE', 'evidence' => $this->evidence($container)];
        }
        $ready = false;
        for ($i = 0; $i < (int) config('social_publication.instagram_poll_attempts'); $i++) {
            $state = $this->meta->instagramContainerStatus($token, $containerId);
            $code = $state['body']['status_code'] ?? null;
            if ($state['status'] === 200 && $code === 'FINISHED') { $ready = true; break; }
            if ($state['status'] === 200 && in_array($code, ['ERROR', 'EXPIRED'], true)) {
                return ['outcome' => 'definitive_failure', 'error_code' => 'IG_CONTAINER_'.$code, 'evidence' => $this->evidence($state)];
            }
            if ($seconds = (int) config('social_publication.instagram_poll_seconds')) { sleep($seconds); }
        }
        if (!$ready) {
            return ['outcome' => 'definitive_failure', 'error_code' => 'IG_CONTAINER_NOT_READY'];
        }
        try {
            abort_unless($ledger->instagramContainer($claim['target_id'], $lease + ['container_id' => $containerId])['may_publish'] ?? false, 409);
        } catch (HttpExceptionInterface) {
            // El portal no autorizó media_publish: no se publicó nada.
            return ['outcome' => 'definitive_failure', 'error_code' => 'PORTAL_REFUSED_PUBLISH'];
        }
        $published = $this->meta->instagramPublish($token, $account, $containerId);
        $media = $published['body']['id'] ?? null;
        if ($published['status'] !== 200 || !is_string($media) || !preg_match('/^[0-9]{5,32}$/D', $media)) {
            return ['outcome' => 'uncertain', 'error_code' => 'IG_PUBLISH_UNKNOWN', 'evidence' => $this->evidence($published)];
        }
        $permalink = $this->meta->instagramPermalink($token, $media);
        $url = $permalink['body']['permalink'] ?? '';
        if ($permalink['status'] === 200 && ($permalink['body']['id'] ?? null) === $media && preg_match('~^https://www\.instagram\.com/(?:p|reel)/[A-Za-z0-9_-]+/$~D', $url)) {
            return ['outcome' => 'success', 'external_post_id' => $media, 'external_url' => $url];
        }
        return ['outcome' => 'uncertain', 'external_post_id' => $media, 'error_code' => 'IG_PERMALINK_UNVERIFIED', 'evidence' => $this->evidence($permalink)];
    }

    private function rejected(array $response): bool
    {
        return $response['status'] >= 400 && $response['status'] < 500 && $response['status'] !== 408 && isset($response['body']['error']);
    }

    /** Evidencia saneada para el log: estado HTTP y error de Meta, sin cabeceras ni tokens. */
    private function evidence(array $response): array
    {
        $error = $response['body']['error'] ?? [];
        return array_filter(['http_status' => $response['status'], 'exception' => $response['exception'] ?? null,
            'meta_code' => $error['code'] ?? null, 'meta_subcode' => $error['error_subcode'] ?? null,
            'meta_message' => isset($error['message']) ? mb_substr((string) $error['message'], 0, 300) : null], fn ($v) => $v !== null);
    }

    private function finish(array $result): array
    {
        try {
            AuditLog::log('social_publication_run', (int) ($result['target_id'] ?? 0), 'run', [], array_intersect_key($result,
                array_flip(['kind', 'provider', 'trigger', 'content_id', 'title', 'status', 'message', 'external_url', 'error_code'])));
        } catch (\Throwable $e) {
            Log::warning('[social-publications] No se pudo registrar la corrida', ['exception' => class_basename($e)]);
        }
        if (in_array($result['status'], ['failed', 'uncertain', 'error'], true)) {
            $this->alert('Publicación en '.SocialPublicationSettings::PROVIDERS[$result['provider']].': '.$result['status'], [
                'Tipo: '.SocialPublicationSettings::KINDS[$result['kind']].' #'.($result['content_id'] ?? '-').' '.($result['title'] ?? ''),
                'Origen: '.($result['trigger'] === 'schedule' ? 'programado' : 'manual'),
                'Detalle: '.$result['message'],
            ]);
        }
        return $result;
    }

    public function alert(string $subject, array $lines): void
    {
        $to = $this->settings->get('alert_email');
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) { return; }
        try { Mail::send(new SocialPublicationAlertMail($to, $subject, $lines, route('social-publications.index'))); }
        catch (\Throwable $e) { Log::warning('[social-publications] No se pudo enviar el aviso', ['exception' => class_basename($e)]); }
    }

    /** Verifica que cada token configurado siga sirviendo para su cuenta. No publica nada. */
    public function checkTokens(bool $notify = true): array
    {
        $results = [];
        $targets = ['facebook' => [substr(config('social_publication.destination'), strlen('facebook:page:')), 'id,name'],
            'instagram' => [substr(config('social_publication.instagram_destination'), strlen('instagram:account:')), 'id,username']];
        $status = $this->credentials->status();
        foreach ($targets as $provider => [$id, $fields]) {
            if (!$status[$provider]['configured']) { continue; }
            $token = $this->credentials->token($provider);
            $response = $token ? $this->meta->node($token, $id, $fields) : ['status' => 0, 'body' => []];
            $ok = $response['status'] === 200 && ($response['body']['id'] ?? null) === $id;
            $name = $response['body']['name'] ?? $response['body']['username'] ?? null;
            $error = $ok ? null : ($response['body']['error']['message'] ?? ($token ? 'Sin respuesta válida de Meta (HTTP '.$response['status'].').' : 'No se pudo descifrar el token.'));
            if (!$status[$provider]['inherited']) { $this->credentials->recordCheck($provider, $ok, $name, $error); }
            $results[$provider] = ['ok' => $ok, 'name' => $name, 'error' => $error];
            if (!$ok && $notify) {
                $this->alert('Token de Meta inválido para '.SocialPublicationSettings::PROVIDERS[$provider], ['Error: '.$error, 'Cargá un token nuevo desde el panel.']);
            }
        }
        return $results;
    }
}
