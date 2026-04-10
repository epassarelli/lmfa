<?php

namespace Tests\Feature\Pasarela;

use App\Jobs\Publication\PublishToProviderJob;
use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PublicationAttempt;
use App\Models\PublicationRequest;
use App\Models\PublicationTarget;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * PC-07-HU-03: Tests del job PublishToProviderJob y conectores.
 *
 * Cubre:
 *  - NativePortalConnector: marca contenido como published, registra attempt exitoso
 *  - NativePortalConnector: falla si el contenido no existe
 *  - Job: omite si target ya está published (idempotencia)
 *  - Job: marca failed si connector falla
 *  - FacebookConnector: publica exitosamente (Http mocked)
 *  - FacebookConnector: maneja error de API (Http mocked)
 *  - Job: registra PublicationAttempt por cada ejecución
 */
class PublishToProviderJobTest extends TestCase
{
    use DatabaseTransactions;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUserWithOrg(): array
    {
        $user = User::factory()->create();
        $org  = Organization::create([
            'type' => 'productora',
            'name' => 'Job Test Org ' . uniqid(),
            'slug' => 'job-org-' . uniqid(),
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
            'title'            => 'Job Festival ' . uniqid(),
            'slug'             => 'job-festival-' . uniqid(),
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addDays(7),
        ]);
    }

    private function makePublicationRequest(Event $event, User $user, array $overrides = []): PublicationRequest
    {
        return PublicationRequest::create(array_merge([
            'content_type'         => Event::class,
            'content_id'           => $event->id,
            'requested_by'         => $user->id,
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_portal_social'  => false,
            'wants_own_social'     => false,
            'status'               => 'pending',
        ], $overrides));
    }

    private function makeTarget(PublicationRequest $req, array $overrides = []): PublicationTarget
    {
        return PublicationTarget::create(array_merge([
            'publication_request_id' => $req->id,
            'provider'               => 'native_portal',
            'destination_type'       => 'article',
            'template_variant'       => 'default',
            'status'                 => 'pending',
            'priority'               => 10,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // NativePortalConnector
    // -------------------------------------------------------------------------

    public function test_native_portal_connector_marks_event_as_published(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event   = $this->makeEvent($org);
        $request = $this->makePublicationRequest($event, $user);
        $target  = $this->makeTarget($request);

        $job = new PublishToProviderJob($target->id);
        $job->handle();

        // Target should be marked success
        $this->assertEquals('success', $target->fresh()->status);

        // Event should be marked published
        $this->assertEquals('published', $event->fresh()->editorial_status);
        $this->assertNotNull($event->fresh()->published_at);

        // A PublicationAttempt should exist
        $this->assertDatabaseHas('publication_attempts', [
            'publication_target_id' => $target->id,
            'status'                => 'success',
            'attempt_number'        => 1,
        ]);
    }

    public function test_native_portal_connector_fails_when_content_not_found(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event   = $this->makeEvent($org);
        $request = $this->makePublicationRequest($event, $user);

        // Target points to a non-existent content_id
        $badRequest = PublicationRequest::create([
            'content_type'         => Event::class,
            'content_id'           => 999999,
            'requested_by'         => $user->id,
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_portal_social'  => false,
            'wants_own_social'     => false,
            'status'               => 'pending',
        ]);
        $target = $this->makeTarget($badRequest);

        $job = new PublishToProviderJob($target->id);
        $job->handle();

        $this->assertEquals('failed', $target->fresh()->status);

        $this->assertDatabaseHas('publication_attempts', [
            'publication_target_id' => $target->id,
            'status'                => 'failed',
            'error_code'            => 'CONTENT_NOT_FOUND',
        ]);
    }

    // -------------------------------------------------------------------------
    // Idempotencia
    // -------------------------------------------------------------------------

    public function test_job_skips_already_published_target(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event   = $this->makeEvent($org);
        $request = $this->makePublicationRequest($event, $user);
        $target  = $this->makeTarget($request, ['status' => 'published']);

        $job = new PublishToProviderJob($target->id);
        $job->handle();

        // No attempt should be recorded because job skipped
        $count = PublicationAttempt::where('publication_target_id', $target->id)->count();
        $this->assertEquals(0, $count);
    }

    // -------------------------------------------------------------------------
    // Job con target inexistente
    // -------------------------------------------------------------------------

    public function test_job_handles_non_existent_target_gracefully(): void
    {
        // Should not throw, just log and return
        $job = new PublishToProviderJob(999999);
        $job->handle(); // Should complete without exception
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // PublicationAttempt número incremental
    // -------------------------------------------------------------------------

    public function test_attempt_number_increments_per_target(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event   = $this->makeEvent($org);
        $request = $this->makePublicationRequest($event, $user);

        // Create a second event for a second attempt
        $event2   = $this->makeEvent($org);
        $request2 = $this->makePublicationRequest($event2, $user);
        $target2  = $this->makeTarget($request2);

        // Insert a previous attempt manually
        PublicationAttempt::create([
            'publication_target_id' => $target2->id,
            'attempt_number'        => 1,
            'started_at'            => now()->subMinutes(5),
            'finished_at'           => now()->subMinutes(4),
            'request_payload_json'  => '{}',
            'status'                => 'failed',
            'is_retryable'          => true,
        ]);

        // Reset target to pending for retry
        $target2->update(['status' => 'pending']);

        $job = new PublishToProviderJob($target2->id);
        $job->handle();

        $lastAttempt = PublicationAttempt::where('publication_target_id', $target2->id)
            ->orderBy('attempt_number', 'desc')
            ->first();

        $this->assertEquals(2, $lastAttempt->attempt_number);
    }

    // -------------------------------------------------------------------------
    // FacebookConnector (Http mocked)
    // -------------------------------------------------------------------------

    public function test_facebook_connector_publishes_successfully(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $account = SocialAccount::create([
            'owner_type'          => get_class($user),
            'owner_id'            => $user->id,
            'provider'            => 'facebook',
            'account_name'        => 'Mi Página FB',
            'account_external_id' => 'page_123',
            'token_encrypted'     => 'fake_access_token',
            'status'              => 'active',
        ]);

        $request = $this->makePublicationRequest($event, $user, ['wants_portal_publish' => false]);
        $target  = $this->makeTarget($request, [
            'provider'          => 'facebook',
            'social_account_id' => $account->id,
            'destination_type'  => 'feed',
            'template_variant'  => 'facebook_default',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['id' => 'page_123_post_456'], 200),
        ]);

        $job = new PublishToProviderJob($target->id);
        $job->handle();

        $this->assertEquals('success', $target->fresh()->status);

        $this->assertDatabaseHas('publication_attempts', [
            'publication_target_id' => $target->id,
            'status'                => 'success',
            'external_post_id'      => 'page_123_post_456',
        ]);
    }

    public function test_facebook_connector_handles_api_error(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $account = SocialAccount::create([
            'owner_type'          => get_class($user),
            'owner_id'            => $user->id,
            'provider'            => 'facebook',
            'account_name'        => 'Mi Página FB',
            'account_external_id' => 'page_err',
            'token_encrypted'     => 'fake_token',
            'status'              => 'active',
        ]);

        $request = $this->makePublicationRequest($event, $user, ['wants_portal_publish' => false]);
        $target  = $this->makeTarget($request, [
            'provider'          => 'facebook',
            'social_account_id' => $account->id,
            'destination_type'  => 'feed',
            'template_variant'  => 'facebook_default',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => ['code' => 190, 'message' => 'Invalid OAuth access token'],
            ], 400),
        ]);

        $job = new PublishToProviderJob($target->id);
        $job->handle();

        $this->assertEquals('failed', $target->fresh()->status);

        $this->assertDatabaseHas('publication_attempts', [
            'publication_target_id' => $target->id,
            'status'                => 'failed',
            'error_code'            => '190',
        ]);
    }
}
