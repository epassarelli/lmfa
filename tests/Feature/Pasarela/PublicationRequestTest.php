<?php

namespace Tests\Feature\Pasarela;

use App\Models\Event;
use App\Models\News;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PublicationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PC-07-HU-01: Tests de solicitud de publicación multicanal.
 *
 * Cubre:
 *  - acceso restringido a invitados
 *  - visualización del formulario de creación
 *  - envío exitoso de solicitud (portal only)
 *  - envío con programación de fecha
 *  - autorización: usuario sin relación con el contenido es rechazado
 *  - visualización del detalle de solicitud
 *  - listado de solicitudes propias
 *  - otro usuario no puede ver solicitudes ajenas
 */
class PublicationRequestTest extends TestCase
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
            'name' => 'Test Org',
            'slug' => 'test-org',
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
            'title'            => 'Festival Test',
            'slug'             => 'festival-test',
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addDays(7),
        ]);
    }

    private function makeNews(Organization $org): News
    {
        return News::create([
            'organization_id'  => $org->id,
            'title'            => 'Noticia Test',
            'slug'             => 'noticia-test',
            'editorial_status' => 'approved',
        ]);
    }

    // -------------------------------------------------------------------------
    // Acceso de invitados
    // -------------------------------------------------------------------------

    public function test_guest_cannot_access_create_form(): void
    {
        $this->get(route('pasarela.publication-requests.create', [
                'content_type' => 'event',
                'content_id'   => 1,
            ]))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_index(): void
    {
        $this->get(route('pasarela.publication-requests.index'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Formulario de creación
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_see_create_form_for_event(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->actingAs($user)
            ->get(route('pasarela.publication-requests.create', [
                'content_type' => 'event',
                'content_id'   => $event->id,
            ]))
            ->assertOk()
            ->assertViewIs('pasarela.publication_requests.create')
            ->assertSee($event->title);
    }

    public function test_create_form_shows_connected_social_accounts(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        \App\Models\SocialAccount::create([
            'owner_type'          => get_class($user),
            'owner_id'            => $user->id,
            'provider'            => 'facebook',
            'account_name'        => 'Mi Página',
            'account_external_id' => 'fb123',
            'token_encrypted'     => 'tok',
            'status'              => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('pasarela.publication-requests.create', [
                'content_type' => 'event',
                'content_id'   => $event->id,
            ]))
            ->assertOk()
            ->assertSee('Mi Página');
    }

    // -------------------------------------------------------------------------
    // Envío (store)
    // -------------------------------------------------------------------------

    public function test_user_can_create_portal_only_publication_request(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->actingAs($user)
            ->post(route('pasarela.publication-requests.store'), [
                'content_type'         => 'event',
                'content_id'           => $event->id,
                'mode'                 => 'portal_only',
                'wants_portal_publish' => '1',
                'wants_own_social'     => '0',
                'wants_portal_social'  => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('publication_requests', [
            'content_type'         => Event::class,
            'content_id'           => $event->id,
            'requested_by'         => $user->id,
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
        ]);
    }

    public function test_publication_request_creates_portal_target(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $this->actingAs($user)
            ->post(route('pasarela.publication-requests.store'), [
                'content_type'         => 'event',
                'content_id'           => $event->id,
                'mode'                 => 'portal_only',
                'wants_portal_publish' => '1',
            ]);

        $req = PublicationRequest::where('content_id', $event->id)->first();
        $this->assertNotNull($req);
        $this->assertDatabaseHas('publication_targets', [
            'publication_request_id' => $req->id,
            'provider'               => 'native_portal',
        ]);
    }

    public function test_user_can_schedule_publication_request(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);
        $scheduled = now()->addDays(3)->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->post(route('pasarela.publication-requests.store'), [
                'content_type'         => 'event',
                'content_id'           => $event->id,
                'mode'                 => 'portal_only',
                'wants_portal_publish' => '1',
                'scheduled_at'         => $scheduled,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('publication_requests', [
            'content_id' => $event->id,
        ]);
    }

    public function test_user_cannot_publish_content_from_unrelated_org(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $user = User::factory()->create();

        $otherOrg = Organization::create([
            'type' => 'productora',
            'name' => 'Otra Org',
            'slug' => 'otra-org-' . uniqid(),
        ]);
        $event = $this->makeEvent($otherOrg);

        $this->actingAs($user)
            ->post(route('pasarela.publication-requests.store'), [
                'content_type'         => 'event',
                'content_id'           => $event->id,
                'mode'                 => 'portal_only',
                'wants_portal_publish' => '1',
            ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('pasarela.publication-requests.store'), [])
            ->assertSessionHasErrors(['content_type', 'content_id', 'mode']);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_user_can_see_own_publication_request(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $req = PublicationRequest::create([
            'content_type'         => Event::class,
            'content_id'           => $event->id,
            'requested_by'         => $user->id,
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_portal_social'  => false,
            'wants_own_social'     => false,
            'status'               => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('pasarela.publication-requests.show', $req))
            ->assertOk()
            ->assertViewIs('pasarela.publication_requests.show');
    }

    public function test_other_user_cannot_see_publication_request(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

        $req = PublicationRequest::create([
            'content_type'         => Event::class,
            'content_id'           => $event->id,
            'requested_by'         => $user->id,
            'mode'                 => 'portal_only',
            'wants_portal_publish' => true,
            'wants_portal_social'  => false,
            'wants_own_social'     => false,
            'status'               => 'pending',
        ]);

        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('pasarela.publication-requests.show', $req));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_shows_only_own_requests(): void
    {
        [$user, $org] = $this->makeUserWithOrg();
        $event = $this->makeEvent($org);

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

        $otherUser = User::factory()->create();

        // El otro usuario NO debería ver la solicitud
        $this->actingAs($otherUser)
            ->get(route('pasarela.publication-requests.index'))
            ->assertOk()
            ->assertDontSee('Festival Test');

        // El dueño SÍ la ve
        $this->actingAs($user)
            ->get(route('pasarela.publication-requests.index'))
            ->assertOk()
            ->assertSee('Event');
    }
}
