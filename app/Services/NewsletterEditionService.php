<?php

namespace App\Services;

use App\Jobs\SendNewsletterJob;
use App\Mail\NewsletterEditionMail;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Event;
use App\Models\Interprete;
use App\Models\News;
use App\Models\NewsletterEdition;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Arma, revisa, aprueba y envía las ediciones del newsletter. Reglas centrales:
 *  - el borrador se arma solo, pero nunca se envía sin aprobación explícita;
 *  - al aprobar se congela el contenido (lo enviado es exactamente lo revisado);
 *  - una edición enviada es inmutable y no se envía dos veces.
 */
class NewsletterEditionService
{
    /** Cuántos candidatos se ofrecen por sección y cuántos vienen marcados de entrada. */
    private const POOL = 15;
    private const DEFAULT_PICKS = ['news' => 5, 'events' => 5, 'albums' => 3, 'songs' => 5, 'artists' => 3];

    /** Días hacia adelante que cubre la agenda de eventos. */
    public const AGENDA_DAYS = 14;

    public static function keyFor(Carbon $periodStart): string
    {
        return 'weekly-'.$periodStart->format('o-\WW');
    }

    /** Período de la «semana pasada» (lunes a domingo). */
    public static function lastWeek(): array
    {
        return [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()];
    }

    // ------------------------------------------------------------------ armado

    /** Crea el borrador del período (idempotente por período). Devuelve null si no hay contenido. */
    public function buildDraft(Carbon $start, Carbon $end, ?User $creator = null, bool $allowEmpty = false): ?NewsletterEdition
    {
        $key = self::keyFor($start);

        if ($existing = NewsletterEdition::where('edition_key', $key)->first()) {
            return $existing;
        }

        $candidates = $this->candidates($start, $end);
        $selection = collect(self::DEFAULT_PICKS)->map(fn ($n, $type) => array_slice(array_column($candidates[$type], 'id'), 0, $n))->all();

        if (! $allowEmpty && collect($selection)->flatten()->isEmpty()) {
            return null;
        }

        return NewsletterEdition::create([
            'edition_key' => $key,
            'subject' => 'Lo mejor de la semana en el Folklore Argentino',
            'intro' => 'Hola, este es el resumen de lo más destacado de la semana en nuestro portal de folklore:',
            'status' => NewsletterEdition::DRAFT,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'selection' => $selection,
            'created_by' => $creator?->id,
        ]);
    }

    /**
     * Contenido disponible para el período, por sección: [{id, title, meta, url}].
     *
     * @return array<string, array<int, array{id:int,title:string,meta:?string,url:string}>>
     */
    public function candidates(Carbon $start, Carbon $end): array
    {
        $range = [$start->copy()->startOfDay(), $end->copy()->endOfDay()];

        $news = News::publishedVisible()
            ->where(fn ($q) => $q->whereBetween('published_at', $range)->orWhere(fn ($i) => $i->whereNull('published_at')->whereBetween('created_at', $range)))
            ->latest('published_at')->limit(self::POOL)->get()
            ->map(fn ($n) => $this->item($n->id, $n->titulo ?? $n->title, null, route('noticias.show', $n->slug)));

        $events = Event::publiclyVisible()->whereNotNull('slug')
            ->whereBetween('start_at', [now()->startOfDay(), now()->addDays(self::AGENDA_DAYS)->endOfDay()])
            ->orderBy('start_at')->limit(self::POOL)->get()
            ->map(fn ($e) => $this->item($e->id, $e->title, optional($e->start_at)->format('d/m H:i'), route('cartelera.show', $e->slug)));

        $albums = Album::with('interprete')->where('estado', 1)->whereBetween('created_at', $range)->latest()->limit(self::POOL)->get()
            ->map(fn ($a) => $this->item($a->id, $a->album, $a->interprete?->interprete, route('artista.disco', ['interprete' => $a->interprete?->slug ?? 'varios', 'slug' => $a->slug])));

        $songs = Cancion::with('interprete')->where('estado', 1)->whereBetween('created_at', $range)->latest()->limit(self::POOL)->get()
            ->map(fn ($c) => $this->item($c->id, $c->cancion, $c->interprete?->interprete, route('artista.cancion', ['interprete' => $c->interprete?->slug ?? 'varios', 'cancion' => $c->slug])));

        $artists = Interprete::where('estado', 1)->whereBetween('created_at', $range)->latest()->limit(self::POOL)->get()
            ->map(fn ($i) => $this->item($i->id, $i->interprete, null, route('artista.show', $i->slug)));

        return [
            'news' => $news->all(),
            'events' => $events->all(),
            'albums' => $albums->all(),
            'songs' => $songs->all(),
            'artists' => $artists->all(),
        ];
    }

    /**
     * Resuelve la selección a ítems actuales y visibles, en el orden de cada sección.
     *
     * @return array<string, array<int, array{id:int,title:string,meta:?string,url:string}>>
     */
    public function resolve(NewsletterEdition $edition): array
    {
        $candidates = $this->candidates($edition->period_start, $edition->period_end);
        $selection = $edition->selection ?? [];
        $resolved = [];

        foreach (array_keys(NewsletterEdition::SECTIONS) as $type) {
            $ids = array_map('intval', $selection[$type] ?? []);
            $items = array_values(array_filter($candidates[$type], fn ($item) => in_array($item['id'], $ids, true)));

            if ($items) {
                $resolved[$type] = $items;
            }
        }

        return $resolved;
    }

    /** Datos listos para dibujar el correo: congelados si la edición está aprobada o enviada. */
    public function content(NewsletterEdition $edition): array
    {
        return $edition->snapshot ?? $this->resolve($edition);
    }

    // ------------------------------------------------------------------ edición

    public function update(NewsletterEdition $edition, array $data): NewsletterEdition
    {
        $this->assertEditable($edition);

        $selection = [];
        foreach (array_keys(NewsletterEdition::SECTIONS) as $type) {
            $selection[$type] = array_values(array_map('intval', $data['selection'][$type] ?? []));
        }

        $edition->update([
            'subject' => $data['subject'],
            'intro' => $data['intro'] ?? null,
            'selection' => $selection,
        ]);

        return $edition;
    }

    public function approve(NewsletterEdition $edition, User $by, ?Carbon $scheduledAt = null): NewsletterEdition
    {
        $this->assertEditable($edition);

        $snapshot = $this->resolve($edition);

        if (blank($edition->subject)) {
            throw ValidationException::withMessages(['subject' => 'Falta el asunto.']);
        }

        if ($snapshot === []) {
            throw ValidationException::withMessages(['selection' => 'Elegí al menos un contenido para la edición.']);
        }

        DB::transaction(function () use ($edition, $snapshot, $by, $scheduledAt) {
            // Un enlace propio por ítem, para contar clics (solo contadores, sin datos personales).
            $edition->links()->delete();

            foreach ($snapshot as $type => $items) {
                foreach ($items as $index => $item) {
                    $link = $edition->links()->create(['section' => $type, 'title' => $item['title'], 'url' => $item['url']]);
                    $snapshot[$type][$index]['link_id'] = $link->id;
                }
            }

            $edition->update([
                'status' => NewsletterEdition::APPROVED,
                'snapshot' => $snapshot,
                'approved_by' => $by->id,
                'approved_at' => now(),
                'scheduled_at' => $scheduledAt,
            ]);
        });

        return $edition;
    }

    public function unapprove(NewsletterEdition $edition): NewsletterEdition
    {
        if (! $edition->isApproved()) {
            throw ValidationException::withMessages(['status' => 'Solo se puede volver a borrador una edición aprobada que todavía no se envió.']);
        }

        $edition->links()->delete();
        $edition->update(['status' => NewsletterEdition::DRAFT, 'snapshot' => null, 'approved_by' => null, 'approved_at' => null, 'scheduled_at' => null]);

        return $edition;
    }

    // ------------------------------------------------------------------ envío

    /** Envío de prueba a una dirección. No registra envíos ni cambia el estado. */
    public function sendTest(NewsletterEdition $edition, string $email): void
    {
        $preview = new NewsletterSubscriber(['email' => $email, 'token' => 'prueba']);

        Mail::to($email)->send(new NewsletterEditionMail($edition, $preview, $this->content($edition), true));

        $edition->forceFill(['test_sent_at' => now()])->save();
    }

    /** Despacha una edición aprobada a los suscriptores confirmados. Devuelve cuántos destinatarios. */
    public function send(NewsletterEdition $edition): int
    {
        if (! $edition->isApproved()) {
            throw ValidationException::withMessages(['status' => 'Solo se puede enviar una edición aprobada que todavía no se envió.']);
        }

        // Se marca como enviada ANTES de despachar: una segunda llamada no puede volver a enviarla.
        $recipients = NewsletterSubscriber::deliverable()->count();
        $edition->update(['status' => NewsletterEdition::SENT, 'sent_at' => now(), 'recipients_count' => $recipients]);

        NewsletterSubscriber::deliverable()->chunkById(100, function ($subscribers) use ($edition) {
            foreach ($subscribers as $subscriber) {
                SendNewsletterJob::dispatch($subscriber->id, $edition->id);
            }
        });

        return $recipients;
    }

    /** Envía las ediciones aprobadas cuya hora programada ya llegó. */
    public function dispatchScheduled(): int
    {
        $sent = 0;

        NewsletterEdition::query()
            ->where('status', NewsletterEdition::APPROVED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get()
            ->each(function (NewsletterEdition $edition) use (&$sent) {
                $this->send($edition);
                $sent++;
            });

        return $sent;
    }

    private function assertEditable(NewsletterEdition $edition): void
    {
        if (! $edition->isDraft()) {
            throw ValidationException::withMessages(['status' => 'Solo se puede modificar una edición en borrador.']);
        }
    }

    private function item(int $id, ?string $title, ?string $meta, string $url): array
    {
        return ['id' => $id, 'title' => (string) $title, 'meta' => $meta, 'url' => $url];
    }
}
