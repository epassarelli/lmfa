<?php

namespace App\Services;

use App\Models\Classified;
use App\Models\ContactMessage;
use App\Models\DataDeletionRequest;
use App\Models\EntityClaimRequest;
use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\Organization;
use App\Models\PublicationRequest;
use App\Models\User;
use App\Support\ModerationQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Qué requiere atención del administrador (tablero) y las insignias del sidebar. Los datos se cachean
 * 30 segundos y se memoizan por petición; las acciones que cambian un pendiente invalidan la caché.
 */
class AdminAttentionService
{
    public const CACHE_KEY = 'admin.attention.v1';
    public const TTL_SECONDS = 30;
    public const WARNING_DAYS = 3;
    public const DANGER_DAYS = 7;
    public const STALE_CONTACT_DAYS = 14;

    private ?array $memo = null;

    public function data(): array
    {
        return $this->memo ??= Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, fn () => $this->compute());
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->memo = null;
    }

    /**
     * Color de AdminLTE según la antigüedad de lo más viejo pendiente.
     * success = al día · info = reciente · warning = desde hace días · danger = demorado.
     */
    public function urgency(int $count, ?int $oldestTimestamp): string
    {
        if ($count <= 0) {
            return 'success';
        }

        if ($oldestTimestamp === null) {
            return 'info';
        }

        $days = (int) floor((now()->timestamp - $oldestTimestamp) / 86400);

        return match (true) {
            $days >= self::DANGER_DAYS => 'danger',
            $days >= self::WARNING_DAYS => 'warning',
            default => 'info',
        };
    }

    /**
     * Insignia del sidebar para una clave (claims, contacts, review, classifieds).
     *
     * @return array{count:int, color:string}
     */
    public function badge(string $key): array
    {
        $entry = $this->data()[$key] ?? null;

        if (! $entry) {
            return ['count' => 0, 'color' => 'secondary'];
        }

        $count = (int) ($entry['count'] ?? $entry['total'] ?? 0);

        return ['count' => $count, 'color' => $this->urgency($count, $entry['oldest'] ?? null)];
    }

    /**
     * Tarjetas de "Requiere tu atención", ya con color, detalle y enlace.
     *
     * @return array<int, array{key:string, title:string, count:int, color:string, detail:string, url:string, icon:string}>
     */
    public function cards(): array
    {
        $d = $this->data();
        $age = fn (?int $ts) => $ts ? 'la más antigua espera hace '.\Illuminate\Support\Carbon::createFromTimestamp($ts)->locale('es')->diffForHumans(now(), \Carbon\CarbonInterface::DIFF_ABSOLUTE) : '';

        $reviewDetail = collect($d['review']['items'])->map(fn ($n, $label) => "$label $n")->implode(' · ');

        return [
            [
                'key' => 'claims', 'title' => 'Solicitudes de ficha', 'count' => $d['claims']['count'],
                'color' => $this->urgency($d['claims']['count'], $d['claims']['oldest']),
                'detail' => $d['claims']['count'] ? trim("{$d['claims']['links']} de vínculo · {$d['claims']['creations']} de alta. ".$age($d['claims']['oldest'])) : 'Todo al día',
                'url' => route('backend.claims.index'), 'icon' => 'fas fa-user-check',
            ],
            [
                'key' => 'contacts', 'title' => 'Mensajes sin leer', 'count' => $d['contacts']['count'],
                'color' => $this->urgency($d['contacts']['count'], $d['contacts']['oldest']),
                'detail' => $d['contacts']['count'] ? ucfirst($age($d['contacts']['oldest'])) : 'Todo al día',
                'url' => route('backend.contact-messages.index'), 'icon' => 'fas fa-envelope',
            ],
            [
                'key' => 'review', 'title' => 'Contenido a revisar', 'count' => $d['review']['total'],
                'color' => $this->urgency($d['review']['total'], $d['review']['oldest']),
                'detail' => $d['review']['total'] ? $reviewDetail : 'Todo al día',
                'url' => route('backend.moderation.index'), 'icon' => 'fas fa-tasks',
            ],
            [
                'key' => 'classifieds', 'title' => 'Clasificados por moderar', 'count' => $d['classifieds']['count'],
                'color' => $this->urgency($d['classifieds']['count'], $d['classifieds']['oldest']),
                'detail' => $d['classifieds']['count'] ? ucfirst($age($d['classifieds']['oldest'])) : 'Todo al día',
                'url' => route('backend.classifieds.index'), 'icon' => 'fas fa-bullhorn',
            ],
        ];
    }

    private function compute(): array
    {
        $claims = EntityClaimRequest::query()->pending()
            ->selectRaw("COUNT(*) as total, COALESCE(SUM(kind = 'create'), 0) as creations, MIN(created_at) as oldest")->first();

        $contacts = ContactMessage::query()->status(ContactMessage::NEW)
            ->selectRaw('COUNT(*) as total, MIN(fecha_envio) as oldest')->first();

        $classifieds = Classified::query()->pendiente()
            ->selectRaw('COUNT(*) as total, MIN(created_at) as oldest')->first();

        $review = ModerationQueue::counts();

        $organizations = Organization::query()
            ->selectRaw(
                "COALESCE(SUM(contact_stage = 'prospect'), 0) as prospects,
                 COALESCE(SUM(contact_stage = 'email_obtained'), 0) as with_email,
                 COALESCE(SUM(contact_stage = 'contacted'), 0) as contacted,
                 COALESCE(SUM(contact_stage = 'contacted' AND last_contacted_at < ?), 0) as stale",
                [now()->subDays(self::STALE_CONTACT_DAYS)]
            )->first();

        $since = now()->subDays(7);

        return [
            'claims' => [
                'count' => (int) $claims->total,
                'creations' => (int) $claims->creations,
                'links' => (int) $claims->total - (int) $claims->creations,
                'oldest' => $this->timestamp($claims->oldest),
            ],
            'contacts' => ['count' => (int) $contacts->total, 'oldest' => $this->timestamp($contacts->oldest)],
            'review' => ['total' => $review['total'], 'items' => array_filter($review['items']), 'oldest' => null],
            'classifieds' => ['count' => (int) $classifieds->total, 'oldest' => $this->timestamp($classifieds->oldest)],
            'alerts' => $this->alerts(),
            'organizations' => [
                'prospects' => (int) $organizations->prospects,
                'with_email' => (int) $organizations->with_email,
                'contacted' => (int) $organizations->contacted,
                'stale' => (int) $organizations->stale,
            ],
            'activity' => [
                'users' => User::query()->where('created_at', '>=', $since)->count(),
                'newsletter' => NewsletterSubscriber::query()->deliverable()->where('confirmed_at', '>=', $since)->count(),
                'claims' => EntityClaimRequest::query()->where('created_at', '>=', $since)->count(),
                'messages' => ContactMessage::query()->where('fecha_envio', '>=', $since)->where('status', '!=', ContactMessage::SPAM)->count(),
                'events_upcoming' => Event::query()->where('editorial_status', 'published')->whereBetween('start_at', [now(), now()->addDays(7)])->count(),
            ],
        ];
    }

    /**
     * Problemas técnicos u operativos que solo se muestran si existen.
     *
     * @return array<int, array{label:string, count:int, route:?string}>
     */
    private function alerts(): array
    {
        $alerts = [];

        $failedPublications = PublicationRequest::query()->where('status', PublicationRequest::STATUS_FAILED)->count();
        if ($failedPublications > 0) {
            $alerts[] = ['label' => 'Publicaciones de la pasarela con error', 'count' => $failedPublications, 'route' => 'pasarela.publication-requests.index'];
        }

        $deletions = DataDeletionRequest::query()->whereIn('status', [DataDeletionRequest::STATUS_PENDING, DataDeletionRequest::STATUS_FAILED])->count();
        if ($deletions > 0) {
            $alerts[] = ['label' => 'Solicitudes de eliminación de datos sin completar', 'count' => $deletions, 'route' => null];
        }

        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        if ($failedJobs > 0) {
            $alerts[] = ['label' => 'Trabajos fallidos en la cola', 'count' => $failedJobs, 'route' => null];
        }

        return $alerts;
    }

    private function timestamp($value): ?int
    {
        return $value ? \Illuminate\Support\Carbon::parse($value)->timestamp : null;
    }
}
