<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\EntityClaimRequest;
use App\Models\Event;
use App\Models\Interprete;
use App\Models\NewsletterSubscriber;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Support\InitialsAvatar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MyAccountTest extends TestCase
{
    use DatabaseTransactions;

    private function user(?string $role = 'colaborador', array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        if ($role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    private function artist(?int $userId = null): Interprete
    {
        return Interprete::create(['interprete' => 'Artista '.uniqid(), 'slug' => 'a-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => $userId ?? $this->baseUserId()]);
    }

    // ---------------- Avatar ----------------

    public function test_initials_follow_simple_rules_and_are_safe(): void
    {
        $this->assertSame('MG', InitialsAvatar::initials('María González'));
        $this->assertSame('MP', InitialsAvatar::initials('María del Carmen Pérez'));
        $this->assertSame('JU', InitialsAvatar::initials('Juan'));
        $this->assertSame('ÁÑ', InitialsAvatar::initials('álvaro ñandú'));
        $this->assertSame('?', InitialsAvatar::initials('   '));
        $this->assertSame('?', InitialsAvatar::initials('<>'));
        $this->assertSame('SB', InitialsAvatar::initials('<script> bad'));
    }

    public function test_avatar_is_a_stable_escaped_svg_data_uri(): void
    {
        $this->assertSame(InitialsAvatar::color(7), InitialsAvatar::color(7));
        $this->assertContains(InitialsAvatar::color(12345), ['#1d4ed8', '#0f766e', '#b45309', '#9d174d', '#6d28d9', '#b91c1c', '#15803d', '#334155']);

        $uri = InitialsAvatar::dataUri('Ana Ruiz', 3);
        $this->assertStringStartsWith('data:image/svg+xml;charset=UTF-8,', $uri);
        $svg = rawurldecode(substr($uri, strlen('data:image/svg+xml;charset=UTF-8,')));
        $this->assertStringContainsString('>AR</text>', $svg);
        $this->assertStringNotContainsString('<script', InitialsAvatar::svg('<script>alert(1)</script> x', 1));
    }

    // ---------------- Menu de usuario ----------------

    public function test_the_user_menu_shows_avatar_role_and_my_data_button(): void
    {
        $user = $this->user('colaborador', ['name' => 'Valeria Quiroga']);
        $user->assignRole(Role::findOrCreate('verificado', 'web'));

        $html = $this->actingAs($user)->get(route('backend.my-entities.index'))->assertOk()
            ->assertSee('Valeria Quiroga')
            ->assertSee('Colaborador verificado')
            ->assertSee('Mis datos')
            ->assertSee('desde')
            ->getContent();

        $this->assertStringContainsString('data:image/svg+xml', $html);
        $this->assertStringContainsString('href="'.url('/admin/mi-cuenta').'"', $html);
    }

    // ---------------- Pagina ----------------

    public function test_the_page_needs_a_session_and_works_for_everyone_even_with_the_access_flag(): void
    {
        $this->get(route('backend.account.edit'))->assertRedirect(route('login'));

        config(['backoffice_access.enabled' => true]);

        foreach ([null, 'colaborador', 'administrador'] as $role) {
            $this->actingAs($this->user($role))->get(route('backend.account.edit'))->assertOk()->assertSee('Mis datos')->assertSee('Cambiar contraseña');
        }
    }

    public function test_email_is_read_only_and_there_is_no_user_id_in_the_urls(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get(route('backend.account.edit'))->assertOk()->assertSee($user->email)->assertSee('escribinos');

        foreach (['backend.account.edit', 'backend.account.update', 'backend.account.password', 'backend.account.newsletter'] as $name) {
            $this->assertStringNotContainsString('{', \Illuminate\Support\Facades\Route::getRoutes()->getByName($name)->uri());
        }
    }

    // ---------------- Datos personales ----------------

    public function test_a_user_updates_only_name_and_phone(): void
    {
        $user = $this->user('colaborador', ['name' => 'Nombre Viejo', 'phone' => null]);
        $email = $user->email;

        $this->actingAs($user)->put(route('backend.account.update'), [
            'name' => 'Nombre Nuevo', 'phone' => '+54 351 5550000',
            'email' => 'otro@example.test', 'status' => 'suspended', 'is_verified_publisher' => 1, 'roles' => ['administrador'], 'password' => 'hackeada123', 'google_id' => 'x',
        ])->assertSessionHasNoErrors()->assertRedirect(route('backend.account.edit'));

        $fresh = $user->fresh();
        $this->assertSame('Nombre Nuevo', $fresh->name);
        $this->assertSame('+54 351 5550000', $fresh->phone);
        $this->assertSame($email, $fresh->email);
        $this->assertNotSame('suspended', $fresh->status);
        $this->assertFalse((bool) $fresh->is_verified_publisher);
        $this->assertNull($fresh->google_id);
        $this->assertTrue(Hash::check('password', $fresh->password));
        $this->assertFalse($fresh->hasRole('administrador'));
    }

    public function test_profile_validation_errors_go_to_their_own_bag(): void
    {
        $user = $this->user();

        $this->actingAs($user)->put(route('backend.account.update'), ['name' => ''])->assertSessionHasErrorsIn('profile', 'name');
        $this->put(route('backend.account.update'), ['name' => str_repeat('a', 300)])->assertSessionHasErrorsIn('profile', 'name');
    }

    public function test_it_never_edits_another_user_even_if_asked_to(): void
    {
        $user = $this->user();
        $other = $this->user('colaborador', ['name' => 'Otra Persona']);

        $this->actingAs($user)->put(route('backend.account.update'), ['name' => 'Soy yo', 'id' => $other->id, 'user_id' => $other->id])->assertSessionHasNoErrors();

        $this->assertSame('Otra Persona', $other->fresh()->name);
        $this->assertSame('Soy yo', $user->fresh()->name);
    }

    // ---------------- Contrasena ----------------

    public function test_password_change_requires_the_current_password_and_the_new_rules(): void
    {
        $user = $this->user();
        $hash = $user->password;
        $this->actingAs($user);

        $this->put(route('backend.account.password'), ['current_password' => 'incorrecta', 'password' => 'nuevaclave1', 'password_confirmation' => 'nuevaclave1'])
            ->assertSessionHasErrorsIn('password', 'current_password');
        $this->put(route('backend.account.password'), ['current_password' => 'password', 'password' => 'corta', 'password_confirmation' => 'corta'])
            ->assertSessionHasErrorsIn('password', 'password');
        $this->put(route('backend.account.password'), ['current_password' => 'password', 'password' => 'nuevaclave1', 'password_confirmation' => 'distinta1'])
            ->assertSessionHasErrorsIn('password', 'password');
        $this->put(route('backend.account.password'), ['current_password' => 'password', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrorsIn('password', 'password');

        $this->assertSame($hash, $user->fresh()->password);
    }

    public function test_password_change_works_and_the_new_password_logs_in(): void
    {
        $user = $this->user();
        $token = $user->remember_token;

        $this->actingAs($user)->put(route('backend.account.password'), ['current_password' => 'password', 'password' => 'nuevaclave1', 'password_confirmation' => 'nuevaclave1'])
            ->assertSessionHasNoErrors()->assertRedirect(route('backend.account.edit'));

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('nuevaclave1', $fresh->password));
        $this->assertNotSame($token, $fresh->remember_token);

        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'nuevaclave1'])->assertRedirect();
        $this->assertAuthenticatedAs($fresh);
    }

    public function test_password_attempts_are_throttled(): void
    {
        $this->actingAs($this->user());

        for ($i = 0; $i < 6; $i++) {
            $this->put(route('backend.account.password'), ['current_password' => 'mala', 'password' => 'nuevaclave1', 'password_confirmation' => 'nuevaclave1']);
        }

        $this->put(route('backend.account.password'), ['current_password' => 'mala', 'password' => 'nuevaclave1', 'password_confirmation' => 'nuevaclave1'])->assertStatus(429);
    }

    // ---------------- Resumen ----------------

    public function test_the_summary_lists_fichas_claims_and_pending_reviews(): void
    {
        $owner = $this->user();
        $artist = $this->artist();
        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => 'active']);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'is_primary' => true]);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $owner->id, 'role' => 'owner', 'status' => 'active']);

        $requester = $this->user();
        EntityClaimRequest::create(['user_id' => $requester->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'relationship' => 'press', 'message' => 'Soy prensa del artista, puedo acreditarlo.', 'status' => 'pending']);
        EntityClaimRequest::create(['user_id' => $owner->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'relationship' => 'artist', 'message' => 'Soy el artista, puedo acreditarlo.', 'status' => 'approved']);

        $this->actingAs($owner)->get(route('backend.account.edit'))->assertOk()
            ->assertSee($artist->interprete)
            ->assertSee('0 pendientes · 1 aprobadas · 0 rechazadas')
            ->assertSee('Tenés 1 solicitud(es) para revisar');

        $this->actingAs($requester)->get(route('backend.account.edit'))->assertOk()
            ->assertSee('1 pendientes · 0 aprobadas · 0 rechazadas')
            ->assertDontSee('para revisar en tus fichas');
    }

    public function test_my_content_counts_only_my_own_items_by_status(): void
    {
        $me = $this->user();
        $other = $this->user();
        $artist = $this->artist();
        $mk = fn (int $userId, int $estado) => Album::create(['album' => 'Disco '.uniqid(), 'slug' => 'd-'.uniqid(), 'anio' => '2024', 'interprete_id' => $artist->id, 'user_id' => $userId, 'estado' => $estado, 'visitas' => 0]);
        $mk($me->id, 1);
        $mk($me->id, 0);
        $mk($me->id, 0);
        $mk($other->id, 1);
        Event::create(['title' => 'Evento '.uniqid(), 'slug' => 'e-'.uniqid(), 'body' => '<p>x</p>', 'start_at' => now()->addWeek(), 'editorial_status' => 'draft', 'status' => 'active', 'modality' => 'presencial', 'created_by' => $me->id]);

        $rows = collect(app(\App\Services\AccountSummaryService::class)->content($me))->keyBy('label');

        $this->assertSame(['total' => 3, 'published' => 1, 'review' => 2], collect($rows['Discos'])->only(['total', 'published', 'review'])->all());
        $this->assertSame(['total' => 1, 'published' => 0, 'review' => 1], collect($rows['Eventos'])->only(['total', 'published', 'review'])->all());
        $this->assertFalse($rows->has('Canciones'));

        $this->actingAs($me)->get(route('backend.account.edit'))->assertOk()->assertSee('Discos')->assertSee('Eventos');
        $this->actingAs($this->user())->get(route('backend.account.edit'))->assertOk()->assertSee('Todavía no cargaste contenido');
    }

    // ---------------- Newsletter ----------------

    public function test_newsletter_subscription_can_be_toggled_from_the_account(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        $this->put(route('backend.account.newsletter'), ['subscribe' => 1])->assertSessionHasNoErrors();
        $sub = NewsletterSubscriber::where('email', $user->email)->firstOrFail();
        $this->assertSame('active', $sub->status);
        $this->assertSame('account', $sub->source);
        $this->assertSame($user->id, (int) $sub->user_id);
        $this->get(route('backend.account.edit'))->assertSee('Suscripto')->assertSee('Darme de baja');

        $this->put(route('backend.account.newsletter'), ['subscribe' => 0])->assertSessionHasNoErrors();
        $sub->refresh();
        $this->assertSame('unsubscribed', $sub->status);
        $this->assertNotNull($sub->unsubscribed_at);

        $this->put(route('backend.account.newsletter'), ['subscribe' => 1]);
        $this->assertSame('active', $sub->fresh()->status);
        $this->assertNull($sub->fresh()->unsubscribed_at);
        $this->assertSame(1, NewsletterSubscriber::where('email', $user->email)->count());
    }

    public function test_an_existing_subscription_keeps_its_original_source(): void
    {
        $user = $this->user();
        NewsletterSubscriber::create(['email' => $user->email, 'status' => 'unsubscribed', 'source' => 'footer', 'unsubscribed_at' => now()]);

        $this->actingAs($user)->put(route('backend.account.newsletter'), ['subscribe' => 1]);

        $this->assertSame('footer', NewsletterSubscriber::where('email', $user->email)->value('source'));
    }

    // ---------------- Ultimo ingreso ----------------

    public function test_last_login_is_recorded_once_in_a_while(): void
    {
        $user = $this->user();
        $this->assertNull($user->last_login_at);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $first = $user->fresh()->last_login_at;
        $this->assertNotNull($first);

        // Un segundo ingreso inmediato no vuelve a escribir.
        $this->post('/logout');
        \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update(['last_login_at' => now()->subMinute()]);
        $stored = $user->fresh()->last_login_at;
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertEquals($stored->timestamp, $user->fresh()->last_login_at->timestamp);

        // Despues de 10 minutos si.
        $this->post('/logout');
        \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update(['last_login_at' => now()->subMinutes(30)]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertTrue($user->fresh()->last_login_at->gt(now()->subMinutes(2)));
    }

    // ---------------- CRUD de Usuarios (reglas compartidas) ----------------

    public function test_admin_user_form_shares_the_rules_and_handles_the_phone(): void
    {
        $admin = $this->user('administrador');
        $this->actingAs($admin)->get(route('users.create'))->assertOk()->assertSee('name="phone"', false);

        $payload = fn (array $extra = []) => array_merge([
            'name' => 'Nueva Persona', 'email' => 'nueva.'.uniqid().'@example.test', 'phone' => '351 000000',
            'password' => 'clavelarga1', 'password_confirmation' => 'clavelarga1', 'roles' => ['colaborador'],
        ], $extra);

        $this->post(route('users.store'), $payload(['password' => '123456', 'password_confirmation' => '123456']))->assertSessionHasErrors('password');

        $ok = $payload();
        $this->post(route('users.store'), $ok)->assertSessionHasNoErrors();
        $created = User::where('email', $ok['email'])->firstOrFail();
        $this->assertSame('351 000000', $created->phone);

        $this->put(route('users.update', $created), ['name' => 'Renombrada', 'email' => $created->email, 'phone' => '999', 'roles' => ['colaborador']])->assertSessionHasNoErrors();
        $this->assertSame('999', $created->fresh()->phone);
        $this->assertSame('Renombrada', $created->fresh()->name);
    }

    // ---------------- Paginas publicas: nada personalizado ----------------

    public function test_public_pages_never_render_data_of_the_logged_in_user(): void
    {
        $user = $this->user('colaborador', ['name' => 'Zulma Quispe Unica']);

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('Zulma Quispe Unica', $html);
        $this->assertStringNotContainsString($user->email, $html);
        $this->assertStringNotContainsString('data:image/svg+xml;charset=UTF-8,%3Csvg', $html);
    }
}
