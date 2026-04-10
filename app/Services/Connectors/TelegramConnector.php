<?php

namespace App\Services\Connectors;

use App\Models\PublicationTarget;
use Illuminate\Support\Facades\Http;

class TelegramConnector extends BaseConnector
{
    /**
     * Publish content to a Telegram channel via Bot API.
     * Expects account_external_id = channel username or chat_id (e.g. @mifolklore or -1001234567890).
     * access_token = Bot token.
     */
    public function publish(PublicationTarget $target): array
    {
        $account = $target->socialAccount;
        $content  = $this->resolveContent($target);

        if (!$content) {
            return $this->errorResult('CONTENT_NOT_FOUND', 'El contenido no fue encontrado.', false);
        }

        $botToken  = $account->token_encrypted;
        $chatId    = $account->account_external_id;

        $text = $this->buildMessage($content);

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        try {
            $response = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                $payload
            );
            $body = $response->json();

            if ($response->successful() && ($body['ok'] ?? false)) {
                $msgId      = $body['result']['message_id'] ?? null;
                $externalUrl = null;
                // Build public channel link if username is available
                if (str_starts_with($chatId, '@')) {
                    $channel     = ltrim($chatId, '@');
                    $externalUrl = "https://t.me/{$channel}/{$msgId}";
                }

                $attempt = $this->recordAttempt($target, $payload, [
                    'success'          => true,
                    'external_post_id' => (string) $msgId,
                    'external_url'     => $externalUrl,
                    'response'         => $body,
                ]);

                return ['success' => true, 'attempt_id' => $attempt->id];
            }

            $errMsg = $body['description'] ?? 'Error desconocido de Telegram';

            $attempt = $this->recordAttempt($target, $payload, [
                'success'      => false,
                'error_code'   => (string) ($body['error_code'] ?? 'TG_ERROR'),
                'error_message'=> $errMsg,
                'is_retryable' => true,
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

    protected function buildMessage(object $content): string
    {
        $title   = $content->title ?? '';
        $excerpt = $content->excerpt ?? ($content->subtitle ?? '');

        // Add public URL if resolvable
        $url = null;
        if (method_exists($content, 'getTable')) {
            $slug = $content->slug ?? null;
            if ($slug) {
                try {
                    $url = $content->getTable() === 'events'
                        ? route('cartelera.show', $slug)
                        : route('noticias.show', $slug);
                } catch (\Exception $e) {}
            }
        }

        $line1 = "<b>" . htmlspecialchars($title) . "</b>";
        $line2 = $excerpt ? "\n" . htmlspecialchars($excerpt) : '';
        $line3 = $url ? "\n\n🔗 {$url}" : '';

        return $line1 . $line2 . $line3;
    }

    protected function errorResult(string $code, string $msg, bool $retryable = false): array
    {
        return ['success' => false, 'error_code' => $code, 'error_message' => $msg, 'is_retryable' => $retryable];
    }
}
