<?php

namespace App\Services;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Defensas del formulario de contacto sin captcha visible: trampa de tiempo (token firmado),
 * honeypot, límite por email, detección de duplicados y filtro de enlaces. Los límites por IP los
 * aplica el middleware `throttle` de la ruta.
 */
class ContactFormGuard
{
    /** Token firmado con el momento en que se mostró el formulario. */
    public function token(): string
    {
        return Crypt::encryptString((string) now()->timestamp);
    }

    /** Mensaje de error si el token es inválido, demasiado rápido o vencido; null si está bien. */
    public function tokenError(?string $token): ?string
    {
        try {
            $issuedAt = (int) Crypt::decryptString((string) $token);
        } catch (\Throwable) {
            return 'No pudimos validar el formulario. Recargá la página y volvé a intentar.';
        }

        $age = now()->timestamp - $issuedAt;

        if ($age < (int) config('contact.min_seconds', 4)) {
            return 'Enviaste el formulario demasiado rápido. Esperá unos segundos y volvé a intentar.';
        }

        if ($age > (int) config('contact.max_age_seconds', 7200)) {
            return 'El formulario venció. Revisá tu mensaje y volvé a enviarlo.';
        }

        return null;
    }

    /** Campo trampa: una persona no lo ve ni lo completa. */
    public function isHoneypot(Request $request): bool
    {
        return filled($request->input('website'));
    }

    public function looksLikeSpam(string $message): bool
    {
        return preg_match_all('#https?://|www\.#i', $message) > (int) config('contact.spam_link_limit', 3);
    }

    /** Hash de la IP (HMAC con la clave de la app): sirve para analizar abuso sin guardar la IP. */
    public function ipHash(?string $ip): ?string
    {
        return $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null;
    }

    public function isDuplicate(string $email, string $message): bool
    {
        return ContactMessage::query()
            ->where('email', $email)
            ->where('fecha_envio', '>=', now()->subMinutes((int) config('contact.duplicate_window_minutes', 10)))
            ->where('mensaje', $message)
            ->exists();
    }

    public function emailLimited(string $email): bool
    {
        return RateLimiter::tooManyAttempts($this->emailKey($email), (int) config('contact.max_per_email_hour', 3));
    }

    public function hitEmail(string $email): void
    {
        RateLimiter::hit($this->emailKey($email), 3600);
    }

    private function emailKey(string $email): string
    {
        return 'contact-email:'.sha1(Str::lower($email));
    }
}
