<?php

namespace App\Services\Publication;

use App\Models\Event;
use App\Models\News;
use App\Models\PublicationRequest;
use App\Models\PublicationTarget;
use App\Models\SocialAccount;
use App\Jobs\Publication\PublishToProviderJob;
use Illuminate\Support\Facades\Auth;

class PublicationService
{
    /**
     * Create a PublicationRequest for the given content
     * and dispatch jobs for each target channel.
     *
     * @param  string  $contentType  Fully-qualified class name: Event::class | News::class
     * @param  int     $contentId
     * @param  array   $options  (mode, wants_portal_publish, wants_portal_social, wants_own_social, scheduled_at)
     * @return PublicationRequest
     */
    public function createRequest(string $contentType, int $contentId, array $options = [], ?int $userId = null): PublicationRequest
    {
        $request = PublicationRequest::create([
            'content_type'        => $contentType,
            'content_id'          => $contentId,
            'requested_by'        => $userId ?? Auth::id(),
            'mode'                => $options['mode'] ?? 'portal_only',
            'wants_portal_publish' => $options['wants_portal_publish'] ?? true,
            'wants_portal_social' => $options['wants_portal_social'] ?? false,
            'wants_own_social'    => $options['wants_own_social'] ?? false,
            'scheduled_at'        => $options['scheduled_at'] ?? null,
            'status'              => 'pending',
        ]);

        $this->generateTargets($request, $options);

        return $request;
    }

    /**
     * Generate PublicationTarget rows for each channel in the request.
     */
    protected function generateTargets(PublicationRequest $request, array $options): void
    {
        // 1. Portal nativo
        if ($request->wants_portal_publish) {
            $request->targets()->create([
                'provider'         => 'native_portal',
                'social_account_id'=> null,
                'destination_type' => 'article',
                'template_variant' => 'default',
                'scheduled_at'     => $request->scheduled_at,
                'status'           => 'pending',
                'priority'         => 10,
            ]);
        }

        // 2. Redes propias del publicador
        if ($request->wants_own_social) {
            /** @var \App\Models\User $requester */
            $requester = $request->requester;

            // Find all active social accounts owned by the requesting user
            $accounts = SocialAccount::where('owner_type', get_class($requester))
                ->where('owner_id', $requester->id)
                ->where('status', 'active')
                ->get();

            foreach ($accounts as $account) {
                $request->targets()->create([
                    'provider'          => $account->provider,
                    'social_account_id' => $account->id,
                    'destination_type'  => 'feed',
                    'template_variant'  => $account->provider . '_default',
                    'scheduled_at'      => $request->scheduled_at,
                    'status'            => 'pending',
                    'priority'          => 5,
                ]);
            }
        }

        // 3. Redes institucionales del portal
        if ($request->wants_portal_social) {
            $portalOrgId = config('app.portal_organization_id');

            if (!$portalOrgId) {
                \Illuminate\Support\Facades\Log::warning('PublicationService: wants_portal_social=true pero portal_organization_id no está configurado en app.php / .env');
                return;
            }

            $portalAccounts = SocialAccount::where('owner_type', \App\Models\Organization::class)
                ->where('owner_id', $portalOrgId)
                ->where('status', 'active')
                ->get();

            foreach ($portalAccounts as $account) {
                $request->targets()->create([
                    'provider'          => $account->provider,
                    'social_account_id' => $account->id,
                    'destination_type'  => 'page',
                    'template_variant'  => $account->provider . '_institutional',
                    'scheduled_at'      => $request->scheduled_at,
                    'status'            => 'pending',
                    'priority'          => 1,
                ]);
            }
        }

        // Dispatch jobs for immediate targets (no scheduled_at)
        $this->dispatchJobs($request);
    }

    /**
     * Dispatch queued jobs for all pending targets.
     */
    public function dispatchJobs(PublicationRequest $request): void
    {
        $targets = $request->targets()
            ->where('status', 'pending')
            ->get();

        foreach ($targets as $target) {
            $delay = $target->scheduled_at
                ? now()->diffInSeconds($target->scheduled_at, false)
                : 0;

            if ($delay > 0) {
                PublishToProviderJob::dispatch($target->id)
                    ->delay($target->scheduled_at);
            } else {
                PublishToProviderJob::dispatch($target->id);
            }
        }
    }
}
