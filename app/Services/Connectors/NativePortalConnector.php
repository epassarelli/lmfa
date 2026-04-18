<?php

namespace App\Services\Connectors;

use App\Models\PublicationTarget;

/**
 * Native Portal connector: marks content as published in the portal.
 * No external HTTP call needed — just updates models and records the attempt.
 */
class NativePortalConnector extends BaseConnector
{
    public function publish(PublicationTarget $target): array
    {
        $content = $this->resolveContent($target);

        if (!$content) {
            $attempt = $this->recordAttempt($target, [], [
                'success'      => false,
                'error_code'   => 'CONTENT_NOT_FOUND',
                'error_message'=> 'El contenido no fue encontrado.',
                'is_retryable' => false,
            ]);
            return ['success' => false, 'attempt_id' => $attempt->id];
        }

        // Mark content as published
        $content->editorial_status = 'published';
        $content->published_at     = now();

        // Legacy portal compatibility
        if (in_array($content->getTable(), ['news', 'events'])) {
            if (method_exists($content, 'getAttributes') && array_key_exists('estado', $content->getAttributes())) {
                $content->estado = 1;
            }
        }

        $content->save();

        $slug = $content->slug ?? null;
        $url  = null;
        if ($slug) {
            try {
                $url = $content->getTable() === 'events'
                    ? route('cartelera.show', $slug)
                    : route('noticias.show', $slug);
            } catch (\Exception $e) {}
        }

        $attempt = $this->recordAttempt($target, ['content_id' => $content->id], [
            'success'          => true,
            'external_post_id' => (string) $content->id,
            'external_url'     => $url,
            'response'         => ['status' => 'published'],
        ]);

        return ['success' => true, 'attempt_id' => $attempt->id];
    }
}
