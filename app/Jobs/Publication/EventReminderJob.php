<?php

namespace App\Jobs\Publication;

use App\Models\Event;
use App\Models\UserNotification;
use App\Services\Publication\PublicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * PC-09-HU-01 — Scheduler Job: re-publicar o recordar eventos próximos.
 * Dispatched daily by the Laravel Scheduler.
 */
class EventReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function handle(): void
    {
        Log::info('[EventReminderJob] Running event reminders...');

        // 1. Events happening within the next 48 hours that are approved and haven't had a reminder sent
        $upcoming = Event::where('editorial_status', 'approved')
            ->where('start_at', '>=', now())
            ->where('start_at', '<=', now()->addHours(48))
            ->whereNull('published_at') // Haven't been portal-published yet
            ->get();

        $service = app(PublicationService::class);

        foreach ($upcoming as $event) {
            Log::info("[EventReminderJob] Processing event #{$event->id}: {$event->title}");

            // Create a publication request if none exists for portal
            $alreadyRequested = \App\Models\PublicationRequest::where('content_type', Event::class)
                ->where('content_id', $event->id)
                ->exists();

            if (!$alreadyRequested) {
                $service->createRequest(Event::class, $event->id, [
                    'mode'                => $event->publication_mode ?? 'portal_only',
                    'wants_portal_publish'=> true,
                ]);
            }

            // Notify creator
            if ($event->created_by) {
                UserNotification::notify(
                    $event->created_by,
                    'event.reminder',
                    '🗓️ Tu evento se publicará pronto',
                    "El evento \"{$event->title}\" está programado para " . ($event->start_at?->format('d/m/Y H:i') ?? 'N/A') . '.'
                );
            }
        }

        Log::info("[EventReminderJob] Processed {$upcoming->count()} events.");
    }
}
