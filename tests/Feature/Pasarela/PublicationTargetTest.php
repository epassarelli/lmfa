<?php

namespace Tests\Feature\Pasarela;

use App\Models\Event;
use App\Models\News;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PublicationRequest;
use App\Models\PublicationTarget;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\Publication\PublicationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * PC-07-HU-02: Tests de generación de publication_targets.
 *
 * Cubre:
 *  - portal_only → 1 target native_portal
 *  - wants_own_social con cuentas activas → targets por cuenta
 *  - wants_own_social sin cuentas → 0 targets de redes propias
 *  - wants_portal_social con cuentas de org → targets institucionales
 *  - modo full → targets combinados
 *  - scheduled_at se propaga a cada target
 *  - prioridades correctas por canal
 *  - jobs encolados por cada target (Queue::fake)
 *  - cuenta inactiva no genera target
 */
class PublicationTargetTest extends TestCase
{
    use DatabaseTransactions;

    private PublicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PublicationService::class);
        Queue::fake();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUserWithOrg(): array
    {
        $user = User::factory()->create();
        $org  = Organization::create([
            'type' => 'productora',
            'name' => 'Target Test Org ' . uniqid(),
            'slug' => 'target-org-' . uniqid(),
        ]);
        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id'         => $user->id,
            'role'            => 'owner',
            'status'          => 'active',
        ]);
        return [$user, $org];
    }

    private function makeEvent(Organization $org): Event
    {
        return Event::create([
            'organization_id'  => $org->id,
            'title'            => 'Target Festival ' . uniqid(),
            'slug'             => 'target-festival-' . uniqid(),
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addDays(7),
        ]);
    }

    private function makeSocialAccount(User $user, string $provider = 'facebook', string $status = 'active'): SocialAccount
    {
        return SocialAccount::create([
            'owner_type'          => get_class($user),
            'owner_id'            => $user->id,
            'provider'            => $provider,
            'account_name'        => ucfirst($provider) . ' Page',
            'account_external_id' => $provider . '_' . uniqid(),
            'token_encrypted'     => 'fake_token',
            'status'              => $status,
        ]);
    }

    private function makeOrgSocialAccount(Organization $org, string $provider = 'facebook'): SocialAccount
    {
        return SocialAccount::create([
            'owner_type'          => get_class($org),
            'owner_id'            => $org->id,
            'provider'            => $provider,
            'account_name'        => 'Portal ' . ucfirst($provider),
            'account_external_id' => 'portal_' . $provider . '_' . uniqid(),
            'token_encrypted'     => 'fake_token',
            'status'              => 'active',
        ]);
    }

    // -------------------------------------------------------------------------
    // Portal only
    // -------------------------------------------------------------------------

    public function test_portal_only_creates_one_native_portal_target(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->actingAs($user);

        $request = $this->service->createRequest(Event::class, $event->id, [
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_own_social'     => false,
            'wants_portal_social'  => false,
        ]);

        $targets = PublicationTarget::where('publication_request_id', $request->id)->get();

        $this->assertCount(1, $targets);
        $this->assertEquals('native_portal', $targets->first()->provider);
        $this->assertEquals('article', $targets->first()->destination_type);
        $this->assertEquals(10, $targets->first()->priority);
        $this->assertEquals('pending', $targets->first()->status);
    }

    // -------------------------------------------------------------------------
    // Own social accounts
    // -------------------------------------------------------------------------

    public function test_wants_own_social_creates_target_per_active_account(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->makeSocialAccount($user, 'facebook');
        $this->makeSocialAccount($user, 'instagram');

        $this->actingAs($user);

        $request = $this->service->createRequest(Event::class, $event->id, [
            'mode'                 => 'social_only',
            'wants_portal_publish' => false,
            'wants_own_social'     => true,
            'wants_portal_social'  => false,
        ]);

        $targets = PublicationTarget::where('publication_request_id', $request->id)->get();

        $this->assertCount(2, $targets);
        $providers = $targets->pluck('provider')->sort()->values()->toArray();
        $this->assertEquals(['facebook', 'instagram'], $providers);

        foreach ($targets as $target) {
            $this->assertEquals('feed', $target->destination_type);
            $this->assertEquals(5, $target->priority);
            $this->assertEquals('pending', $target->status);
        }
    }

    public function test_wants_own_social_without_accounts_creates_no_social_targets(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->actingAs($user);

        $request = $this->service->createRequest(Event::class, $event->id, [
            'mode'                 => 'social_only',
            'wants_portal_publish' => false,
            'wants_own_social'     => true,
            'wants_portal_social'  => false,
        ]);

        $count = PublicationTarget::where('publication_request_id', $request->id)->count();
        $this->assertEquals(0, $count);
    }

    public function test_inactive_social_account_does_not_generate_target(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->makeSocialAccount($user, 'facebook', 'inactive');

        $this->actingAs($user);

        $request = $this->service->createRequest(Event::class, $event->id, [
            'mode'                 => 'social_only',
            'wants_portal_publish' => false,
            'wants_own_social'     => true,
            'wants_portal_social'  => false,
        ]);

        $count = PublicationTarget::where('publication_request_id', $request->id)->count();
        $this->assertEquals(0, $count);
    }

    // -------------------------------------------------------------------------
    // Portal social accounts
    // -------------------------------------------------------------------------

    public function test_wants_portal_social_creates_targets_for_org_accounts(): void
    {
        [$user, $userOrg] = $this->makeUserWithOrg();
        $event = $this->makeEvent($userOrg);

        // Simulate portal's institutional org
        $portalOrg = Organization::create([
            'type' => 'portal',
            'name' => 'Portal MFA',
            'slug' => 'portal-mfa-' . uniqid(),
        ]);
        $this->makeOrgSocialAccount($portalOrg, 'facebook');
        $this->makeOrgSocialAccount($portalOrg, 'telegram');

        $this->actingAs($user);

        $request = $this->service->createRequest(Event::class, $event->id, [
            'mode'                 => 'full',
            'wants_portal_publish' => false,
            'wants_own_social'     => false,
            'wants_portal_social'  => true,
        ]);

        $targets = PublicationTarget::where('publication_request_id', $request->id)->get();

        $this->assertCount(2, $targets);
        foreach ($targets as $target) {
            $this->assertEquals('page', $target->destination_type);
            $this->assertEquals(1, $target->priority);
        }
    }

    // -------------------------------------------------------------------------
    // Full mode
    // -------------------------------------------------------------------------

    public function test_full_mode_creates_portal_and_social_targets(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->makeSocialAccount($user, 'instagram');

        $this->actingAs($user);

        $request = $this->service->createRequest(Event::class, $event->id, [
            'mode'                 => 'full',
            'wants_portal_publish' => true,
            'wants_own_social'     => true,
            'wants_portal_social'  => false,
        ]);

        $targets = PublicationTarget::where('publication_request_id', $request->id)->get();

        // 1 portal + 1 instagram
        $this->assertCount(2, $targets);

        $providers = $targets->pluck('provider')->toArray();
        $this->assertContains('native_portal', $providers);
        $this->assertContains('instagram', $providers);
    }

    // -------------------------------------------------------------------------
    // Scheduled_at propagation
    // -------------------------------------------------------------------------

    public function test_scheduled_at_propagates_to_targets(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);
        $scheduled = now()->addDays(2)->setSecond(0)->setMicrosecond(0);

        $this->actingAs($user);

        $request = $this->service->createRequest(Event::class, $event->id, [
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_own_social'     => false,
            'wants_portal_social'  => false,
            'scheduled_at'         => $scheduled,
        ]);

        $target = PublicationTarget::where('publication_request_id', $request->id)->first();
        $this->assertNotNull($target->scheduled_at);
        $this->assertEquals(
            $scheduled->format('Y-m-d H:i'),
            $target->scheduled_at->format('Y-m-d H:i')
        );
    }

    // -------------------------------------------------------------------------
    // Queue dispatch
    // -------------------------------------------------------------------------

    public function test_jobs_are_dispatched_for_each_target(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->makeSocialAccount($user, 'facebook');

        $this->actingAs($user);

        $this->service->createRequest(Event::class, $event->id, [
            'mode'                 => 'full',
            'wants_portal_publish' => true,
            'wants_own_social'     => true,
            'wants_portal_social'  => false,
        ]);

        Queue::assertPushed(\App\Jobs\Publication\PublishToProviderJob::class, 2);
    }

    public function test_no_jobs_dispatched_when_no_targets(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->actingAs($user);

        $this->service->createRequest(Event::class, $event->id, [
            'mode'                 => 'portal_only',
            'wants_portal_publish' => false,
            'wants_own_social'     => false,
            'wants_portal_social'  => false,
        ]);

        Queue::assertNothingPushed();
    }

    // -------------------------------------------------------------------------
    // News content type
    // -------------------------------------------------------------------------

    public function test_target_generation_works_for_news_content(): void
    {
        [$user, $org] = $this->makeUserWithOrg();

        $news = News::create([
            'organization_id'  => $org->id,
            'title'            => 'Noticia Target ' . uniqid(),
            'slug'             => 'noticia-target-' . uniqid(),
            'editorial_status' => 'approved',
        ]);

        $this->actingAs($user);

        $request = $this->service->createRequest(News::class, $news->id, [
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_own_social'     => false,
            'wants_portal_social'  => false,
        ]);

        $this->assertDatabaseHas('publication_targets', [
            'publication_request_id' => $request->id,
            'provider'               => 'native_portal',
        ]);
    }
}
