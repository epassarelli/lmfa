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

/** Envía una edición aprobada a un suscriptor confirmado, una sola vez. */
class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $subscriberId, public int $editionId)
    {
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
