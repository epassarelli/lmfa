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

    public function legacyExists(string $kind, int $id, string $provider = 'facebook', ?string $destination = null): bool
    {
        $class = $kind === 'news' ? \App\Models\News::class : \App\Models\Event::class;
        $destination ??= config('social_publication.destination');
        $page = substr($destination, strrpos($destination, ':') + 1);
        // Any matching legacy row blocks the pilot until explicitly reconciled.
        return PublicationTarget::where('provider', $provider)->where('executor', 'laravel')
            ->whereHas('request', fn ($q) => $q->where('content_type', $class)->where('content_id', $id))
            ->where(fn ($q) => $q->where('destination_key', $destination)
                ->orWhereHas('socialAccount', fn ($a) => $a->where('account_external_id', $page)))->exists();
    }
}
