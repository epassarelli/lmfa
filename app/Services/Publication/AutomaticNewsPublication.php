<?php

namespace App\Services\Publication;

use App\Models\{News, SocialPublicationEligibility};
use Illuminate\Database\Eloquent\Builder;

class AutomaticNewsPublication
{
    public function activeQuery(string $provider = 'facebook'): Builder
    {
        // Use the same canonical public state as the portal. The legacy estado column may be stale.
        return News::publishedVisible()->where('news.id', '>', ($provider === 'instagram' ? config('social_publication.instagram_after_id') : config('social_publication.news_after_id')));
    }

    public function candidates(string $provider = 'facebook'): Builder
    {
        $destination = config($provider === 'instagram' ? 'social_publication.instagram_destination' : 'social_publication.destination');
        $page = substr($destination, strrpos($destination, ':') + 1);
        return $this->activeQuery($provider)->when($provider === 'instagram', fn ($q) => $q->whereExists(function ($f) {
                $f->selectRaw('1')->from('publication_targets as fb')->where('fb.provider', 'facebook')
                    ->where('fb.destination_key', config('social_publication.destination'))->where('fb.ledger_content_kind', 'news')
                    ->whereColumn('fb.ledger_content_id', 'news.id')->whereIn('fb.status', ['success', 'published']);
            }))->whereNotNull('news.slug')->where('news.slug', '<>', '')
            ->whereNotNull('news.title')->where('news.title', '<>', '')
            ->whereNotExists(function ($q) use ($destination, $provider) {
                $q->selectRaw('1')->from('publication_targets as t')
                    ->where('t.ledger_content_kind', 'news')->whereColumn('t.ledger_content_id', 'news.id')
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
                    ->where('lr.content_type', News::class)->whereColumn('lr.content_id', 'news.id')
                    ->where('lt.provider', $provider)->where('lt.executor', 'laravel')
                    ->where(fn ($d) => $d->where('lt.destination_key', $destination)->orWhere('la.account_external_id', $page));
            })->orderBy('news.id');
    }

    public function facebookPublished(int $id): bool
    {
        return \App\Models\PublicationTarget::where('provider', 'facebook')->where('destination_key', config('social_publication.destination'))
            ->where('ledger_content_kind', 'news')->where('ledger_content_id', $id)->whereIn('status', ['success', 'published'])->exists();
    }

    public function eligibility(News $news, string $provider = 'facebook'): SocialPublicationEligibility
    {
        // GET candidates is read-only: this model is a projection, not a persisted opt-in.
        return new SocialPublicationEligibility(['content_kind' => 'news', 'content_id' => $news->id,
            'provider' => $provider, 'destination_key' => config($provider === 'instagram' ? 'social_publication.instagram_destination' : 'social_publication.destination'),
            'enabled' => true, 'portal_only_override' => true]);
    }
}
