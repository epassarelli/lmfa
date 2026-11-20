<?php

namespace App\Services\Publication;

use App\Models\{Event, SocialPublicationEligibility};
use Illuminate\Database\Eloquent\Builder;

/**
 * Cola automática de Eventos, misma operatoria que Noticias: públicos y vigentes o próximos, con ID mayor al umbral,
 * sin envío previo a ese destino, ordenados por fecha de inicio. Instagram exige éxito previo en Facebook.
 */
class AutomaticEventPublication
{
    private function destination(string $provider): string
    {
        return config($provider === 'instagram' ? 'social_publication.instagram_destination' : 'social_publication.destination');
    }

    public function activeQuery(string $provider = 'facebook'): Builder
    {
        return Event::publiclyVisible()->upcomingOrOngoing()->where('events.id', '>', (int) config('social_publication.events_after_id'));
    }

    public function candidates(string $provider = 'facebook'): Builder
    {
        $destination = $this->destination($provider);
        $page = substr($destination, strrpos($destination, ':') + 1);
        return $this->activeQuery($provider)->when($provider === 'instagram', fn ($q) => $q->whereExists(function ($f) {
                $f->selectRaw('1')->from('publication_targets as fb')->where('fb.provider', 'facebook')
                    ->where('fb.destination_key', config('social_publication.destination'))->where('fb.ledger_content_kind', 'events')
                    ->whereColumn('fb.ledger_content_id', 'events.id')->whereIn('fb.status', ['success', 'published']);
            }))->whereNotNull('events.slug')->where('events.slug', '<>', '')
            ->whereNotNull('events.title')->where('events.title', '<>', '')
            ->whereNotExists(function ($q) use ($destination, $provider) {
                // Igual que Noticias: solo vuelve a la cola lo que quedó en un estado reintentable y seguro.
                $q->selectRaw('1')->from('publication_targets as t')
                    ->where('t.ledger_content_kind', 'events')->whereColumn('t.ledger_content_id', 'events.id')
                    ->where('t.provider', $provider)->where('t.destination_key', $destination)
                    ->whereNot(function ($safe) {
                        $safe->where('t.attempts_count', '<', 3)
                            ->where(fn ($retry) => $retry->whereNull('t.retry_after')->orWhere('t.retry_after', '<=', now()))
                            ->where(function ($state) {
                                $state->whereIn('t.status', ['pending', 'failed', 'cancelled'])
                                    ->orWhere(fn ($reserved) => $reserved->where('t.status', 'reserved')
                                        ->whereNotNull('t.lease_expires_at')->where('t.lease_expires_at', '<=', now()));
                            });
                    });
            })->whereNotExists(function ($q) use ($destination, $page, $provider) {
                $q->selectRaw('1')->from('publication_targets as lt')
                    ->join('publication_requests as lr', 'lr.id', '=', 'lt.publication_request_id')
                    ->leftJoin('social_accounts as la', 'la.id', '=', 'lt.social_account_id')
                    ->where('lr.content_type', Event::class)->whereColumn('lr.content_id', 'events.id')
                    ->where('lt.provider', $provider)->where('lt.executor', 'laravel')
                    ->where(fn ($d) => $d->where('lt.destination_key', $destination)->orWhere('la.account_external_id', $page));
            })->orderBy('events.start_at')->orderBy('events.id');
    }

    public function facebookPublished(int $id): bool
    {
        return \App\Models\PublicationTarget::where('provider', 'facebook')->where('destination_key', config('social_publication.destination'))
            ->where('ledger_content_kind', 'events')->where('ledger_content_id', $id)->whereIn('status', ['success', 'published'])->exists();
    }

    public function eligibility(Event $event, string $provider = 'facebook'): SocialPublicationEligibility
    {
        return new SocialPublicationEligibility(['content_kind' => 'events', 'content_id' => $event->id, 'provider' => $provider,
            'destination_key' => $this->destination($provider), 'enabled' => true, 'portal_only_override' => true]);
    }
}
