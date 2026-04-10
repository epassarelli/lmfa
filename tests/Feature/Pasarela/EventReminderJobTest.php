<?php

namespace Tests\Feature\Pasarela;

use App\Jobs\Publication\EventReminderJob;
use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PublicationRequest;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * PC-09-HU-01: Tests del EventReminderJob (recordatorios automáticos).
 *
 * Cubre:
 *  - procesa eventos próximos sin publication_request → los crea
 *  - omite eventos que ya tienen publication_request
 *  - omite eventos fuera de la ventana 48h
 *  - notifica al creador del evento
 */
class EventReminderJobTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(): User
    {
        $user = User::factory()->create();
        $org  = Organization::create([
            'type' => 'productora',
            'name' => 'Reminder Org ' . uniqid(),
            'slug' => 'reminder-org-' . uniqid(),
        ]);
        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id'         => $user->id,
            'role'            => 'owner',
            'status'          => 'active',
        ]);
        return $user;
    }

    public function test_job_creates_publication_request_for_upcoming_event(): void
    {
        Queue::fake();

        $user = $this->makeUser();

        $event = Event::create([
            'organization_id'  => Organization::where('slug', 'like', 'reminder-org-%')->orderByDesc('id')->first()->id,
            'title'            => 'Upcoming Event ' . uniqid(),
            'slug'             => 'upcoming-event-' . uniqid(),
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addHours(24), // within 48h window
            'created_by'       => $user->id,
        ]);

        // No existing publication request
        $this->assertDatabaseMissing('publication_requests', ['content_id' => $event->id]);

        $job = new EventReminderJob();
        $job->handle();

        $this->assertDatabaseHas('publication_requests', [
            'content_type' => Event::class,
            'content_id'   => $event->id,
        ]);
    }

    public function test_job_skips_event_with_existing_publication_request(): void
    {
        Queue::fake();

        $user = $this->makeUser();
        $org  = Organization::where('slug', 'like', 'reminder-org-%')->orderByDesc('id')->first();

        $event = Event::create([
            'organization_id'  => $org->id,
            'title'            => 'Already Requested ' . uniqid(),
            'slug'             => 'already-requested-' . uniqid(),
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addHours(12),
            'created_by'       => $user->id,
        ]);

        // Pre-create publication request
        PublicationRequest::create([
            'content_type'         => Event::class,
            'content_id'           => $event->id,
            'requested_by'         => $user->id,
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_portal_social'  => false,
            'wants_own_social'     => false,
            'status'               => 'pending',
        ]);

        $countBefore = PublicationRequest::where('content_id', $event->id)->count();

        $job = new EventReminderJob();
        $job->handle();

        // Should not create a second request
        $this->assertEquals($countBefore, PublicationRequest::where('content_id', $event->id)->count());
    }

    public function test_job_ignores_events_outside_48h_window(): void
    {
        Queue::fake();

        $user = $this->makeUser();
        $org  = Organization::where('slug', 'like', 'reminder-org-%')->orderByDesc('id')->first();

        $farEvent = Event::create([
            'organization_id'  => $org->id,
            'title'            => 'Far Future Event ' . uniqid(),
            'slug'             => 'far-future-' . uniqid(),
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addDays(10), // outside 48h window
            'created_by'       => $user->id,
        ]);

        $job = new EventReminderJob();
        $job->handle();

        $this->assertDatabaseMissing('publication_requests', ['content_id' => $farEvent->id]);
    }

    public function test_job_notifies_event_creator(): void
    {
        Queue::fake();

        $user = $this->makeUser();
        $org  = Organization::where('slug', 'like', 'reminder-org-%')->orderByDesc('id')->first();

        $event = Event::create([
            'organization_id'  => $org->id,
            'title'            => 'Notify Test Event ' . uniqid(),
            'slug'             => 'notify-test-' . uniqid(),
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addHours(36),
            'created_by'       => $user->id,
        ]);

        $job = new EventReminderJob();
        $job->handle();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type'    => 'event.reminder',
        ]);
    }
}
