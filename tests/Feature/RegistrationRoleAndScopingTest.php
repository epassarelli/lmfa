<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationRoleAndScopingTest extends TestCase
{
    use DatabaseTransactions;

    private function user(?string ...$roles): User
    {
        $user = User::factory()->create();

        foreach (array_filter($roles) as $role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    private function festival(string $title, User $owner): Festival
    {
        return Festival::create([
            'title' => $title, 'slug' => 'f-'.uniqid(), 'status' => 'draft', 'user_id' => $owner->id,
            'mes_id' => (Mes::first() ?: Mes::create(['nombre' => 'Enero']))->id,
        ]);
    }

    public function test_registering_does_not_assign_any_role(): void
    {
        $this->post('/register', [
            'name' => 'Persona Nueva', 'email' => 'nueva.persona@example.test',
            'password' => 'clave-segura-1', 'password_confirmation' => 'clave-segura-1',
        ]);

        $created = User::where('email', 'nueva.persona@example.test')->first();

        $this->assertNotNull($created);
        $this->assertCount(0, $created->getRoleNames());
    }

    public function test_users_without_role_do_not_see_other_peoples_festivals_in_the_listing(): void
    {
        $other = $this->user('colaborador');
        $this->festival('Festival ajeno reservado', $other);
        $mine = $this->user();
        $this->festival('Festival propio visible', $mine);

        $this->actingAs($mine)->get(route('backend.festivales.index'))->assertOk()
            ->assertSee('Festival propio visible')
            ->assertDontSee('Festival ajeno reservado');

        $this->actingAs($this->user('administrador'))->get(route('backend.festivales.index', ['search' => 'Festival ajeno reservado']))
            ->assertOk()->assertSee('Festival ajeno reservado');
    }

    public function test_users_without_role_only_see_their_own_artists(): void
    {
        $other = $this->user('colaborador');
        Interprete::create(['interprete' => 'Artista Ajeno Reservado', 'slug' => 'ajeno-res-'.uniqid(), 'biografia' => '<p>Bio</p>', 'estado' => 0, 'user_id' => $other->id]);

        $this->actingAs($this->user())->get(route('backend.interpretes.index', ['search' => 'Ajeno Reservado']))
            ->assertOk()->assertDontSee('Artista Ajeno Reservado');
    }

    public function test_direct_creation_of_artists_and_festivals_is_limited_when_the_access_flag_is_on(): void
    {
        config(['backoffice_access.enabled' => true]);

        $collaborator = $this->user('colaborador');
        $verified = $this->user('colaborador', 'verificado');

        foreach (['backend.interpretes.create', 'backend.festivales.create'] as $route) {
            $this->assertTrue($verified->canCreateEntityDirectly(['colaborador']));
            $this->assertFalse($collaborator->canCreateEntityDirectly(['colaborador']));
            $this->assertNotSame(200, $this->actingAs($collaborator)->get(route($route))->status());
        }

        $this->actingAs($verified)->get(route('backend.festivales.create'))->assertOk();
        $this->actingAs($this->user('administrador'))->get(route('backend.interpretes.create'))->assertOk();
    }

    public function test_with_the_flag_off_collaborators_keep_direct_creation(): void
    {
        config(['backoffice_access.enabled' => false]);

        $this->actingAs($this->user('colaborador'))->get(route('backend.festivales.create'))->assertOk();
    }

    public function test_member_panel_shows_own_data_and_an_invitation_when_there_are_no_fichas(): void
    {
        $this->actingAs($this->user())->get(route('dashboard'))->assertOk()
            ->assertSee('data-member="entities"', false)
            ->assertSee('Solicitar una ficha')
            ->assertSee('data-member="claims"', false)
            ->assertDontSee('data-attention=', false)
            ->assertDontSee('Totales del portal');
    }
}
