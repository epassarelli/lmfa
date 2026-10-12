<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeHelpTest extends TestCase
{
    use DatabaseTransactions;

    /** ruta de alta => [sección de ayuda, perfil de imagen o null, tiene bloque SEO] */
    private const FORMS = [
        'backend.interpretes.create' => ['interprete', 'artist', true],
        'backend.discos.create' => ['album', 'album', true],
        'backend.canciones.create' => ['cancion', null, true],
        'backend.festivales.create' => ['festival', 'festival', true],
        'backend.mitos.create' => ['mito', 'mito', true],
        'backend.comidas.create' => ['comida', 'recipe', true],
        'backend.news.create' => ['news', 'news', false],
        'backend.events.create' => ['event', 'event', false],
        'backend.classifieds.create' => ['classified', 'classified', false],
    ];

    private function user(string $role): User
    {
        $user = User::factory()->create();
        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }

    public function test_every_form_explains_how_it_works_and_every_image_field_gives_size_and_format(): void
    {
        $admin = $this->user('administrador');

        foreach (self::FORMS as $route => [$section, $profile, $seo]) {
            $page = $this->actingAs($admin)->get(route($route));
            $page->assertOk()->assertSee('data-form-help="'.$section.'"', false);

            if ($profile) {
                $page->assertSee('data-image-hint="'.$profile.'"', false);
            }

            if ($seo) {
                $page->assertSee('data-seo-box', false)->assertSee('Cómo aparece en Google (opcional)')->assertDontSee('SEO y metadatos');
            }
        }
    }

    public function test_every_listing_explains_why_some_records_are_visible_and_others_not(): void
    {
        $admin = $this->user('administrador');

        foreach (['interpretes' => 'interprete', 'discos' => 'album', 'canciones' => 'cancion', 'festivales' => 'festival', 'events' => 'event', 'news' => 'news', 'mitos' => 'mito', 'comidas' => 'comida', 'classifieds' => 'classified'] as $route => $section) {
            $this->actingAs($admin)->get(route("backend.$route.index"))->assertOk()->assertSee('data-list-help="'.$section.'"', false);
        }
    }

    public function test_the_text_adapts_to_the_role(): void
    {
        $this->actingAs($this->user('administrador'))->get(route('backend.mitos.index'))->assertSee('Ves todos los mitos.');
        $this->actingAs($this->user('administrador'))->get(route('backend.festivales.create'))->assertSee('Podés publicar directamente');

        $collaborator = $this->user('colaborador');
        $this->actingAs($collaborator)->get(route('backend.mitos.index'))->assertSee('Solo ves los mitos que cargaste vos.')->assertSee('Mis fichas');
        $this->actingAs($collaborator)->get(route('backend.mitos.create'))->assertSee('queda en borrador hasta que el equipo lo revise', false);
    }

    public function test_artists_listing_text_matches_the_real_scope(): void
    {
        $collaborator = $this->user('colaborador');

        config(['backoffice_access.enabled' => false, 'entity_management.scope_listings' => false]);
        $this->actingAs($collaborator)->get(route('backend.interpretes.index'))->assertSee('Ves todos los artistas, para poder elegirlos');

        config(['backoffice_access.enabled' => true]);
        $text = $this->actingAs($this->user('verificado'))->get(route('backend.interpretes.index'))->getContent();
        $this->assertStringNotContainsString('Ves todos los artistas, para poder', $text);
    }

    public function test_festival_status_is_not_offered_to_people_who_cannot_publish(): void
    {
        $this->actingAs($this->user('administrador'))->get(route('backend.festivales.create'))->assertSee('<select name="status"', false);

        $this->actingAs($this->user('colaborador'))->get(route('backend.festivales.create'))
            ->assertDontSee('<select name="status"', false)
            ->assertSee('Borrador (lo publica el equipo)');
    }

    public function test_required_fields_match_the_server_rules(): void
    {
        $admin = $this->user('administrador');

        // Noticias: la imagen es obligatoria al crear (NoticiaRequest); Intérpretes: la biografía lo es (InterpreteRequest).
        $this->assertMatchesRegularExpression('/<input type="file" name="foto"[^>]*\srequired\s*>/', $this->actingAs($admin)->get(route('backend.news.create'))->getContent());
        $this->assertMatchesRegularExpression('/<textarea name="biografia"[^>]*\srequired\s/', $this->actingAs($admin)->get(route('backend.interpretes.create'))->getContent());
    }

    public function test_my_fichas_explains_what_a_ficha_is(): void
    {
        $this->actingAs($this->user('colaborador'))->get(route('backend.my-entities.index'))->assertOk()
            ->assertSee('data-fichas-help', false)
            ->assertSee('Responsable')
            ->assertSee('Miembro');
    }

    public function test_classifieds_form_is_in_spanish(): void
    {
        $this->actingAs($this->user('administrador'))->get(route('backend.classifieds.create'))->assertOk()
            ->assertSee('Nuevo aviso clasificado')->assertSee('Categoría')->assertDontSee('Create Classified')->assertDontSee('Contact Info');
    }
}
