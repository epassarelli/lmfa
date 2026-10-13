<?php

namespace App\Jobs;

use App\Mail\NewsletterEditionMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterEdition;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NewsletterBounceDetector;
use App\Services\NewsletterSubscriptionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/** Envía una edición aprobada a un suscriptor confirmado, una sola vez. */
class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Los reintentos por tope de ritmo no cuentan como fallos: solo fallan 3 errores reales dentro de la ventana. */
    public int $tries = 0;

    public int $maxExceptions = 3;

    /** Espera entre reintentos por errores reales (segundos). */
    public array $backoff = [60, 300];

    public function __construct(public int $subscriberId, public int $editionId)
    {
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(12);
    }
    public function handle(): void
    {
        $edition = NewsletterEdition::find($this->editionId);
        $subscriber = NewsletterSubscriber::find($this->subscriberId);

        // Solo ediciones aprobadas/enviadas con contenido congelado, y solo confirmados activos
        // (puede haberse dado de baja entre el despacho y el envío).
        if (! $edition || ! $subscriber || $edition->isDraft() || $edition->snapshot === null || ! $subscriber->isDeliverable()) {
            return;
        }

        // Ritmo: no más de N correos por minuto (límites del proveedor). Los que sobran vuelven a la cola.
        // Con la cola síncrona (tests/desarrollo) no se aplica: release() la dejaría en bucle.
        $limit = (int) config('newsletter.max_per_minute');

        if ($limit > 0 && config('queue.default') !== 'sync') {
            if (RateLimiter::tooManyAttempts('newsletter-send', $limit)) {
                $this->release(30);

                return;
            }

            RateLimiter::hit('newsletter-send', 60);
        }

        // Reserva el envío: si ya existe (reintento o doble despacho) no se manda dos veces.
        $reserved = NewsletterDelivery::query()->insertOrIgnore([
            'subscriber_id' => $subscriber->id,
            'edition_key' => $edition->edition_key,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($reserved === 0) {
            return;
        }

        try {
            Mail::to($subscriber->email)->send(new NewsletterEditionMail($edition, $subscriber, $edition->snapshot));
        } catch (\Throwable $e) {
            // Libera la reserva para que un reintento pueda enviarlo.
            NewsletterDelivery::query()->where('subscriber_id', $subscriber->id)->where('edition_key', $edition->edition_key)->whereNull('sent_at')->delete();

            // Rechazo definitivo de ESA dirección (no existe): se marca y no se reintenta. Cualquier otro
            // error (política, límites, caídas) se relanza para reintentar sin tocar a la persona.
            if (NewsletterBounceDetector::isHardBounce($e)) {
                app(NewsletterSubscriptionService::class)->markBounced($subscriber);
                Log::warning('Newsletter: dirección rebotada.', ['subscriber_id' => $subscriber->id, 'error' => $e->getMessage()]);

                return;
            }

            throw $e;
        }

        NewsletterDelivery::query()->where('subscriber_id', $subscriber->id)->where('edition_key', $edition->edition_key)->update(['sent_at' => now()]);
    }
}
