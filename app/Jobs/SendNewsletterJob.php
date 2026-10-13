<?php

namespace App\Jobs;

use App\Mail\WeeklyNewsletterMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $subscriber;
    public $newsData;

    public function __construct(NewsletterSubscriber $subscriber, array $newsData)
    {
        $this->subscriber = $subscriber;
        $this->newsData = $newsData;
    }

    public function handle(): void
    {
        // Solo confirmados y activos (puede haberse dado de baja entre el despacho y el envío).
        if (! $this->subscriber->fresh()?->isDeliverable()) {
            return;
        }

        $editionKey = $this->newsData['edition_key'] ?? null;

        // Reserva el envío: si ya existe (comando repetido o job reintentado) no se manda dos veces.
        if ($editionKey) {
            $reserved = NewsletterDelivery::query()->insertOrIgnore([
                'subscriber_id' => $this->subscriber->id,
                'edition_key' => $editionKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($reserved === 0) {
                return;
            }
        }

        try {
            Mail::to($this->subscriber->email)->send(new WeeklyNewsletterMail($this->subscriber, $this->newsData));
        } catch (\Throwable $e) {
            // Libera la reserva para que un reintento pueda enviarlo.
            if ($editionKey) {
                NewsletterDelivery::query()->where('subscriber_id', $this->subscriber->id)->where('edition_key', $editionKey)->whereNull('sent_at')->delete();
            }

            throw $e;
        }

        if ($editionKey) {
            NewsletterDelivery::query()->where('subscriber_id', $this->subscriber->id)->where('edition_key', $editionKey)->update(['sent_at' => now()]);
        }
    }
}
