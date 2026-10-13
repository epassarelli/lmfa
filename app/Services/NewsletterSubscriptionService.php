<?php

namespace App\Services;

use App\Mail\ConfirmNewsletterSubscriptionMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Alta, confirmación y baja del newsletter (doble confirmación). Las reglas:
 *  - nadie queda activo sin confirmar desde su propio correo;
 *  - quien se dio de baja solo vuelve confirmando desde su correo (nadie lo reactiva por él);
 *  - un solo mail de confirmación por dirección cada 10 minutos;
 *  - las direcciones rebotadas no se vuelven a escribir.
 */
class NewsletterSubscriptionService
{
    public const RESEND_AFTER_MINUTES = 10;

    public function __construct(private ContactFormGuard $guard)
    {
    }

    /**
     * Pide la suscripción de un email. No revela si ya existía: el llamador responde siempre lo mismo.
     *
     * @return bool true si se envió un mail de confirmación
     */
    public function request(string $email, string $source, ?string $ip = null, ?int $userId = null): bool
    {
        $email = mb_strtolower(trim($email));
        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $email]);

        if ($subscriber->exists && ($subscriber->isDeliverable() || $subscriber->status === NewsletterSubscriber::BOUNCED)) {
            return false;
        }

        if (! $subscriber->exists) {
            $subscriber->status = NewsletterSubscriber::PENDING;
            $subscriber->source = $source;
        }

        $subscriber->user_id = $subscriber->user_id ?? $userId;
        $subscriber->consented_at = now();
        $subscriber->consent_ip_hash = $this->guard->ipHash($ip);
        $subscriber->save();

        return $this->sendConfirmation($subscriber);
    }

    /** Envía (o reenvía) el mail de confirmación respetando el intervalo mínimo. */
    public function sendConfirmation(NewsletterSubscriber $subscriber): bool
    {
        if ($subscriber->status === NewsletterSubscriber::BOUNCED) {
            return false;
        }

        if ($subscriber->last_confirmation_sent_at && $subscriber->last_confirmation_sent_at->gt(now()->subMinutes(self::RESEND_AFTER_MINUTES))) {
            return false;
        }

        $subscriber->forceFill(['last_confirmation_sent_at' => now()])->save();

        try {
            Mail::to($subscriber->email)->send(new ConfirmNewsletterSubscriptionMail($subscriber));

            return true;
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar la confirmación del newsletter.', ['subscriber_id' => $subscriber->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function confirm(NewsletterSubscriber $subscriber): void
    {
        $subscriber->forceFill([
            'status' => NewsletterSubscriber::ACTIVE,
            'confirmed_at' => $subscriber->confirmed_at ?? now(),
            'consented_at' => $subscriber->consented_at ?? now(),
            'unsubscribed_at' => null,
        ])->save();
    }

    public function unsubscribe(NewsletterSubscriber $subscriber): void
    {
        if ($subscriber->status === NewsletterSubscriber::UNSUBSCRIBED) {
            return;
        }

        $subscriber->forceFill(['status' => NewsletterSubscriber::UNSUBSCRIBED, 'unsubscribed_at' => now()])->save();
    }
}
