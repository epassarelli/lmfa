<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Category;
use App\Models\Classified;
use App\Models\Comida;
use App\Models\Contribution;
use App\Models\Interprete;
use App\Models\NewsletterSubscriber;
use App\Models\PublicationTemplate;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Mito;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackendListingDiscoveryTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);

        foreach (['read album', 'create album', 'update album', 'delete album'] as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);

            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        foreach (['read cancion', 'create cancion', 'update cancion', 'delete cancion'] as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);

            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_users_index_supports_search_and_preserves_query_string_in_pagination(): void
    {
        $admin = $this->makeAdmin();

        User::factory()->create(['name' => 'Zed User', 'email' => 'zed@example.com']);

        foreach (range(1, 30) as $index) {
            User::factory()->create([
                'name' => 'Match User '.$index,
                'email' => sprintf('match%02d@example.com', $index),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('users.index', [
            'search' => 'Match User',
            'sort' => 'email',
            'direction' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSee('Match User 1');
        $response->assertDontSee('Zed User');
        $response->assertSee('search=Match', false);
        $response->assertSee('sort=email', false);
        $response->assertSee('direction=asc', false);
    }

    public function test_newsletter_index_supports_filters_sorting_and_query_string_pagination(): void
    {
        $admin = $this->makeAdmin();

        NewsletterSubscriber::create([
            'email' => 'zzz@example.com',
            'name' => 'Otro',
            'status' => 'active',
        ]);

        foreach (range(1, 18) as $index) {
            NewsletterSubscriber::create([
                'email' => sprintf('ana%02d@example.com', $index),
                'name' => 'Ana '.$index,
                'status' => 'active',
            ]);
        }

        NewsletterSubscriber::create([
            'email' => 'ana-baja@example.com',
            'name' => 'Ana Baja',
            'status' => 'unsubscribed',
        ]);

        $response = $this->actingAs($admin)->get(route('backend.newsletter.index', [
            'search' => 'Ana',
            'status' => 'active',
            'sort' => 'email',
            'direction' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSee('ana01@example.com');
        $response->assertDontSee('zzz@example.com');
        $response->assertDontSee('ana-baja@example.com');
        $response->assertSee('status=active', false);
        $response->assertSee('sort=email', false);
    }

    public function test_publication_templates_index_supports_search_and_sort(): void
    {
        $user = User::factory()->create();

        PublicationTemplate::create([
            'provider' => 'telegram',
            'content_type' => 'App\Models\Event',
            'variant_name' => 'zzz_variant',
            'template_text' => 'Zzz',
            'is_active' => true,
        ]);

        PublicationTemplate::create([
            'provider' => 'facebook',
            'content_type' => 'App\Models\News',
            'variant_name' => 'ana_variant',
            'template_text' => 'Ana',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('pasarela.templates.index', [
            'search' => 'ana_variant',
            'sort' => 'variant_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSee('ana_variant');
        $response->assertDontSee('zzz_variant');
    }

    public function test_notifications_index_supports_search_and_sort(): void
    {
        $user = User::factory()->create();

        UserNotification::notify($user->id, 'publication.success', 'Publicado en portal', 'Tu evento fue publicado.');
        UserNotification::notify($user->id, 'event.reminder', 'Recordatorio', 'Tu evento es manana.');
        UserNotification::notify($user->id, 'misc', 'Otra', 'Mensaje irrelevante.');

        $response = $this->actingAs($user)->get(route('pasarela.notifications.index', [
            'search' => 'Recordatorio',
            'sort' => 'title',
            'direction' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSee('Recordatorio');
        $response->assertDontSee('Publicado en portal');
        $response->assertDontSee('Mensaje irrelevante');
    }

    public function test_albums_index_supports_server_side_search_sort_and_pagination_state(): void
    {
        $admin = $this->makeAdmin();
        $interprete = Interprete::create([
            'interprete' => 'Los Buscables',
            'slug' => 'los-buscables',
            'biografia' => 'Biografia de prueba',
            'estado' => 1,
            'visitas' => 0,
        ]);

        Album::create([
            'album' => 'Zeta Disco',
            'slug' => 'zeta-disco',
            'anio' => '1999',
            'visitas' => 5,
            'spotify' => 'spotify:zeta',
            'estado' => 1,
            'user_id' => $admin->id,
            'interprete_id' => $interprete->id,
        ]);

        foreach (range(1, 21) as $index) {
            Album::create([
                'album' => 'Coleccion '.$index,
                'slug' => 'coleccion-'.$index,
                'anio' => (string) (2000 + $index),
                'visitas' => $index,
                'spotify' => 'spotify:coleccion-'.$index,
                'estado' => 1,
                'user_id' => $admin->id,
                'interprete_id' => $interprete->id,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('backend.discos.index', [
            'search' => 'Coleccion',
            'sort' => 'album',
            'direction' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSee('Coleccion 1');
        $response->assertDontSee('Zeta Disco');
        $response->assertSee('sort=album', false);
        $response->assertSee('direction=asc', false);
    }

    public function test_classifieds_index_supports_search_status_filter_and_query_string_pagination(): void
    {
        $admin = $this->makeAdmin();
        $category = Category::create([
            'name' => 'Instrumentos',
            'slug' => 'instrumentos-backend-listing-test',
        ]);

        Classified::create([
            'title' => 'Bombo premium',
            'slug' => 'bombo-premium',
            'description' => 'Aviso visible',
            'location' => 'Salta',
            'price' => 1000,
            'estado' => 'activo',
            'is_active' => true,
            'user_id' => $admin->id,
            'category_id' => $category->id,
        ]);

        Classified::create([
            'title' => 'Guitarra rechazada',
            'slug' => 'guitarra-rechazada',
            'description' => 'No debe aparecer',
            'location' => 'Cordoba',
            'price' => 500,
            'estado' => 'rechazado',
            'is_active' => false,
            'user_id' => $admin->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->get(route('backend.classifieds.index', [
            'search' => 'Bombo',
            'status' => 'activo',
            'sort' => 'title',
            'direction' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSee('Bombo premium');
        $response->assertDontSee('Guitarra rechazada');
        $response->assertSee('value="Bombo"', false);
        $response->assertSee('option value="activo" selected', false);
    }

    public function test_contributions_index_supports_search_status_filter_and_sort(): void
    {
        $admin = $this->makeAdmin();
        $contributor = User::factory()->create([
            'name' => 'Colaborador Uno',
            'email' => 'colaborador@example.com',
        ]);

        Contribution::create([
            'user_id' => $contributor->id,
            'contributable_type' => 'App\Models\News',
            'contributable_id' => null,
            'payload' => ['title' => 'Aporte destacado'],
            'status' => 'pending',
        ]);

        Contribution::create([
            'user_id' => $contributor->id,
            'contributable_type' => 'App\Models\Event',
            'contributable_id' => null,
            'payload' => ['title' => 'Otro aporte'],
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($admin)->get(route('backend.contributions.admin.index', [
            'search' => 'Aporte destacado',
            'status' => 'pending',
            'sort' => 'status',
            'direction' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSee('Aporte destacado');
        $response->assertDontSee('Otro aporte');
        $response->assertSee('value="Aporte destacado"', false);
        $response->assertSee('option value="pending" selected', false);
    }

    public function test_canciones_datatable_supports_async_search_filter_and_sort(): void
    {
        $admin = $this->makeAdmin();
        $interprete = Interprete::create([
            'interprete' => 'Duo Test',
            'slug' => 'duo-test-canciones',
            'biografia' => 'Biografia para canciones',
            'estado' => 1,
            'visitas' => 0,
        ]);

        Cancion::create([
            'cancion' => 'Zamba QA Unica Backend',
            'slug' => 'zamba-qa-unica-backend',
            'letra' => 'Letra uno',
            'visitas' => 10,
            'estado' => 1,
            'user_id' => $admin->id,
            'interprete_id' => $interprete->id,
        ]);

        Cancion::create([
            'cancion' => 'Chacarera pendiente',
            'slug' => 'chacarera-pendiente',
            'letra' => 'Letra dos',
            'visitas' => 2,
            'estado' => 0,
            'user_id' => $admin->id,
            'interprete_id' => $interprete->id,
        ]);

        $response = $this->actingAs($admin)->get(route('backend.canciones.get', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'QA Unica Backend'],
            'status' => 'active',
            'order' => [
                ['column' => 2, 'dir' => 'desc'],
            ],
            'columns' => [
                ['data' => 'cancion', 'name' => 'cancion', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'interprete', 'name' => 'interprete', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'visitas', 'name' => 'visitas', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'estado', 'name' => 'estado', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'acciones', 'name' => 'acciones', 'searchable' => 'false', 'orderable' => 'false'],
            ],
        ]));

        $response->assertOk();
        $response->assertJsonPath('recordsFiltered', 1);
        $response->assertJsonPath('data.0.cancion', 'Zamba QA Unica Backend');
        $response->assertJsonPath('data.0.interprete', 'Duo Test');
    }

    public function test_comidas_index_supports_search_sort_and_query_string_pagination(): void
    {
        $admin = $this->makeAdmin();

        Comida::create([
            'titulo' => 'Locro QA Unico',
            'slug' => 'locro-qa-unico',
            'receta' => 'Receta especial de locro',
            'foto' => 'locro.jpg',
            'visitas' => 25,
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        Comida::create([
            'titulo' => 'Humita secundaria',
            'slug' => 'humita-secundaria',
            'receta' => 'Otra receta',
            'foto' => 'humita.jpg',
            'visitas' => 3,
            'estado' => 0,
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('backend.comidas.index', [
            'search' => 'QA Unico',
            'sort' => 'visitas',
            'direction' => 'desc',
        ]));

        $response->assertOk();
        $response->assertSee('Locro QA Unico');
        $response->assertDontSee('Humita secundaria');
        $response->assertSee('value="QA Unico"', false);
        $response->assertSee('option value="visitas" selected', false);
        $response->assertSee('<option value="desc" selected>Descendente</option>', false);
    }

    public function test_mitos_index_supports_search_sort_and_query_string_pagination(): void
    {
        $admin = $this->makeAdmin();

        Mito::create([
            'titulo' => 'Lobizon QA Unico',
            'slug' => 'lobizon-qa-unico',
            'mito' => 'Historia del lobizon',
            'foto' => 'lobizon.jpg',
            'visitas' => 18,
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        Mito::create([
            'titulo' => 'Pombero secundario',
            'slug' => 'pombero-secundario',
            'mito' => 'Otra historia',
            'foto' => 'pombero.jpg',
            'visitas' => 2,
            'estado' => 0,
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('backend.mitos.index', [
            'search' => 'QA Unico',
            'sort' => 'visitas',
            'direction' => 'desc',
        ]));

        $response->assertOk();
        $response->assertSee('Lobizon QA Unico');
        $response->assertDontSee('Pombero secundario');
        $response->assertSee('value="QA Unico"', false);
        $response->assertSee('option value="visitas" selected', false);
        $response->assertSee('<option value="desc" selected>Descendente</option>', false);
    }
}
