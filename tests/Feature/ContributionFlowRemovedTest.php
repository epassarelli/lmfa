<?php

namespace Tests\Feature;

use App\Models\Cancion;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContributionFlowRemovedTest extends TestCase
{
    use DatabaseTransactions;

    private function user(?string $role = 'colaborador'): User
    {
        $user = User::factory()->create();

        if ($role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    public function test_the_contribution_routes_no_longer_exist(): void
    {
        foreach (['backend.contributions.index', 'backend.contributions.create', 'backend.contributions.store', 'backend.contributions.admin.index', 'backend.contributions.show', 'backend.contributions.approve', 'backend.contributions.reject'] as $name) {
            $this->assertFalse(Route::has($name), "$name debía desaparecer");
        }

        $this->actingAs($this->user('administrador'))->get('/admin/contribuir')->assertNotFound();
        $this->get('/admin/contributions')->assertNotFound();
        $this->get('/admin/contribuir/crear/interprete/1')->assertNotFound();
    }

    public function test_publicar_leads_to_the_guide_and_the_dashboard_still_goes_through_login(): void
    {
        $this->get('/')->assertOk()->assertSee('href="'.route('publish.guide').'"', false);

        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $user = $this->user();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(url('/admin'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_an_authenticated_user_clicking_publicar_enters_admin_directly(): void
    {
        $this->actingAs($this->user('administrador'))->get(route('dashboard'))->assertOk()
            ->assertDontSee('Publicaciones Pendientes')
            ->assertDontSee('Revisar publicaciones');
    }

    public function test_non_admins_land_on_their_member_panel_with_or_without_the_flag(): void
    {
        foreach ([true, false] as $flag) {
            config(['backoffice_access.enabled' => $flag]);

            $this->actingAs($this->user())->get(route('dashboard'))->assertOk()->assertSee('data-member-panel', false);
            $this->actingAs($this->user(null))->get(route('dashboard'))->assertOk()->assertSee('data-member-panel', false);
            $this->actingAs($this->user('administrador'))->get(route('dashboard'))->assertOk()->assertDontSee('data-member-panel', false);
        }
    }

    public function test_with_the_access_flag_off_the_dashboard_is_unchanged_for_collaborators(): void
    {
        $this->actingAs($this->user())->get(route('dashboard'))->assertOk();
    }

    public function test_public_pages_no_longer_offer_correction_suggestions(): void
    {
        $artist = Interprete::create(['interprete' => 'Artista '.uniqid(), 'slug' => 'a-'.uniqid(), 'biografia' => '<p>Biografía de prueba</p>', 'estado' => 1, 'user_id' => $this->user()->id]);

        $html = $this->get(route('artista.show', $artist->slug))->assertOk()->getContent();

        $this->assertStringNotContainsString('Sugerir edicion', $html);
        $this->assertStringNotContainsString('contribuir', $html);
    }

    public function test_the_contributions_table_and_the_points_and_rank_columns_are_gone(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('contributions'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('users', 'points'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('users', 'rank'));
    }
}
