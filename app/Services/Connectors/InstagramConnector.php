<?php

namespace App\Services\Connectors;

use App\Models\PublicationTarget;
use App\Services\Publication\TemplateService;
use Illuminate\Support\Facades\Http;

class InstagramConnector extends BaseConnector
{
    /**
     * Publish content to Instagram via Meta Content Publishing API.
     * Requires: media (image), caption.
     * Flow: 1) Create media container -> 2) Publish container.
     */
    public function publish(PublicationTarget $target): array
    {
        $account = $target->socialAccount;
        $content  = $this->resolveContent($target);

        if (!$content) {
            return $this->errorResult('CONTENT_NOT_FOUND', 'El contenido no fue encontrado.', false);
        }

        $igUserId    = $account->account_external_id;
        $accessToken = $account->token_encrypted;

        $caption  = app(TemplateService::class)->render($target, $content);
        $imageUrl = $this->resolveImageUrl($content);

        if (!$imageUrl) {
            return $this->errorResult('NO_IMAGE', 'Instagram requiere imagen. El contenido no tiene imagen asociada.', false);
        }

        $payload = [
            'caption'      => $caption,
            'image_url'    => $imageUrl,
            'access_token' => $accessToken,
        ];

        try {
            // Step 1: Create media container
            $containerResponse = Http::timeout(15)->post(
                "https://graph.facebook.com/v19.0/{$igUserId}/media",
                $payload
            );
            $containerBody = $containerResponse->json();

            if (!$containerResponse->successful() || !isset($containerBody['id'])) {
                $errCode = $containerBody['error']['code'] ?? 'IG_CONTAINER_ERROR';
                $errMsg  = $containerBody['error']['message'] ?? 'Error al crear contenedor Instagram';

                return $this->recordAndReturn($target, $payload, [
                    'success'      => false,
                    'error_code'   => (string) $errCode,
                    'error_message'=> $errMsg,
                    'is_retryable' => true,
                    'response'     => $containerBody,
                ]);
            }

            $containerId = $containerBody['id'];

            // Step 2: Publish the container
            $publishResponse = Http::timeout(15)->post(
                "https://graph.facebook.com/v19.0/{$igUserId}/media_publish",
                ['creation_id' => $containerId, 'access_token' => $accessToken]
            );
            $publishBody = $publishResponse->json();

            if ($publishResponse->successful() && isset($publishBody['id'])) {
                return $this->recordAndReturn($target, $payload, [
                    'success'          => true,
                    'external_post_id' => $publishBody['id'],
                    'external_url'     => "https://www.instagram.com/p/{$publishBody['id']}/",
                    'response'         => $publishBody,
                ]);
            }

            $errCode = $publishBody['error']['code'] ?? 'IG_PUBLISH_ERROR';
            $errMsg  = $publishBody['error']['message'] ?? 'Error al publicar en Instagram';

            return $this->recordAndReturn($target, $payload, [
                'success'      => false,
                'error_code'   => (string) $errCode,
                'error_message'=> $errMsg,
                'is_retryable' => true,
                'response'     => $publishBody,
            ]);

        } catch (\Exception $e) {
            return $this->recordAndReturn($target, $payload, [
                'success'      => false,
                'error_code'   => 'EXCEPTION',
                'error_message'=> $e->getMessage(),
                'is_retryable' => true,
            ]);
        }
    }

    protected function buildCaption(object $content): string
    {
        $title   = $content->title ?? '';
        $excerpt = $content->excerpt ?? ($content->subtitle ?? '');

        return trim("{$title}\n\n{$excerpt}");
    }

    protected function resolveImageUrl(object $content): ?string
    {
        $images = $content->images ?? collect();
        if ($images->isEmpty()) return null;

        $first = $images->first();
        // Check variants_json for webp, otherwise fall back to original_path
        $variants = $first->variants_json ?? [];
        $path = $variants['webp'] ?? $variants['thumbnail'] ?? $first->original_path ?? null;
        if (!$path) return null;

        return asset('storage/' . $path);
    }

    protected function errorResult(string $code, string $msg, bool $retryable = false): array
    {
        return [
            'success'      => false,
            'error_code'   => $code,
            'error_message'=> $msg,
            'is_retryable' => $retryable,
        ];
    }

    protected function recordAndReturn(PublicationTarget $target, array $payload, array $result): array
    {
        $attempt = $this->recordAttempt($target, $payload, $result);
        return ['success' => $result['success'], 'attempt_id' => $attempt->id];
    }
}
