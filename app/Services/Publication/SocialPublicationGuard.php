<?php

namespace App\Services\Publication;

use App\Models\PublicationTarget;

class SocialPublicationGuard
{
    public function managed(PublicationTarget $target): bool
    {
        return $target->executor === 'n8n' || ($target->provider === 'facebook'
            && 'facebook:page:'.$target->socialAccount?->account_external_id === config('social_publication.destination'));
    }

    public function legacyExists(string $kind, int $id): bool
    {
        $class = $kind === 'news' ? \App\Models\News::class : \App\Models\Event::class;
        $page = substr(config('social_publication.destination'), strlen('facebook:page:'));
        // Any matching legacy row blocks the pilot until explicitly reconciled.
        return PublicationTarget::where('provider', 'facebook')->where('executor', 'laravel')
            ->whereHas('request', fn ($q) => $q->where('content_type', $class)->where('content_id', $id))
            ->where(fn ($q) => $q->where('destination_key', config('social_publication.destination'))
                ->orWhereHas('socialAccount', fn ($a) => $a->where('account_external_id', $page)))->exists();
    }
}
