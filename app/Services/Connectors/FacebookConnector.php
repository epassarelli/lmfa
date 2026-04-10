<?php

namespace App\Services\Connectors;

use App\Models\PublicationTarget;
use App\Services\Publication\TemplateService;
use Illuminate\Support\Facades\Http;

class FacebookConnector extends BaseConnector
{
    /**
     * Publish content to a Facebook page via Graph API.
     */
    public function publish(PublicationTarget $target): array
    {
        $account = $target->socialAccount;
        $content  = $this->resolveContent($target);

        if (!$content) {
            return $this->errorResult('CONTENT_NOT_FOUND', 'El contenido no fue encontrado.', false);
        }

        // Build post text via TemplateService (falls back to title+excerpt)
        $text = app(TemplateService::class)->render($target, $content);

        // Build the request payload
        $payload = [
            'message'      => $text,
            'access_token' => $account->token_encrypted, // Decrypted by cast
        ];

        // Add link if available (for events or news slug)
        $url = $this->buildPublicUrl($content);
        if ($url) {
            $payload['link'] = $url;
        }

        // --- GRAPH API CALL ---
        try {
            $pageId   = $account->account_external_id;
            $apiUrl   = "https://graph.facebook.com/v19.0/{$pageId}/feed";

            $response = Http::timeout(15)->post($apiUrl, $payload);
            $body     = $response->json();

            if ($response->successful() && isset($body['id'])) {
                $attempt = $this->recordAttempt($target, $payload, [
                    'success'          => true,
                    'external_post_id' => $body['id'],
                    'external_url'     => "https://www.facebook.com/{$body['id']}",
                    'response'         => $body,
                ]);

                return ['success' => true, 'attempt_id' => $attempt->id];
            }

            $errCode = $body['error']['code'] ?? 'FB_UNKNOWN';
            $errMsg  = $body['error']['message'] ?? 'Unknown Facebook error';
            // FB error 190 = token expired, retryable after refresh
            $retryable = in_array($errCode, [1, 2, 17, 341]);

            $attempt = $this->recordAttempt($target, $payload, [
                'success'      => false,
                'error_code'   => (string) $errCode,
                'error_message'=> $errMsg,
                'is_retryable' => $retryable,
                'response'     => $body,
            ]);

            return ['success' => false, 'attempt_id' => $attempt->id, 'error' => $errMsg];

        } catch (\Exception $e) {
            $attempt = $this->recordAttempt($target, $payload, [
                'success'      => false,
                'error_code'   => 'EXCEPTION',
                'error_message'=> $e->getMessage(),
                'is_retryable' => true,
            ]);

            return ['success' => false, 'attempt_id' => $attempt->id, 'error' => $e->getMessage()];
        }
    }

    protected function buildPostText(object $content): string
    {
        $title   = $content->title ?? '';
        $excerpt = $content->excerpt ?? ($content->subtitle ?? '');

        return trim("{$title}\n\n{$excerpt}");
    }

    protected function buildPublicUrl(object $content): ?string
    {
        $slug = $content->slug ?? null;
        if (!$slug) return null;

        // Detect type by table name
        if (method_exists($content, 'getTable')) {
            if ($content->getTable() === 'events') {
                return route('cartelera.show', $slug);
            }
            if ($content->getTable() === 'news') {
                return route('noticias.show', $slug);
            }
        }
        return null;
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
}
