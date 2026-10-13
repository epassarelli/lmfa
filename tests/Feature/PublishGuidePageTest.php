<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublishGuidePageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_guide_is_public_indexable_and_explains_the_steps(): void
    {
        $html = $this->get('/como-publicar')->assertOk()
            ->assertSee('Cómo publicar en Mi Folklore Argentino')
            ->assertSee('Creá tu cuenta')
            ->assertSee('Mis fichas')
            ->assertSee('Esperá la aprobación')
            ->assertSee('Revisión y publicación')
            ->assertSee('letras completas')
            ->assertSee(route('register'), false)
            ->assertSee('data-publish-guide', false)
            ->getContent();

        $this->assertStringNotContainsStringIgnoringCase('noindex', $html);
        $this->assertMatchesRegularExpression('#<link rel="canonical" href="https?://[^"]+/como-publicar"#', $html);
    }

    public function test_the_guide_markup_is_the_same_for_visitors_and_logged_in_users(): void
    {
        $visitor = $this->get('/como-publicar')->getContent();

        $user = User::factory()->create();
        Role::findOrCreate('colaborador', 'web');
        $user->assignRole('colaborador');
        $logged = $this->actingAs($user)->get('/como-publicar')->getContent();

        $strip = fn (string $html) => preg_replace('/<meta name="csrf-token"[^>]*>|name="_token" value="[^"]*"|\s+/', '', $html);
        $this->assertStringContainsString('data-publish-guide', $logged);
        $this->assertSame(
            preg_replace('/.*(<article.*<\/article>).*/s', '$1', $strip($visitor)),
            preg_replace('/.*(<article.*<\/article>).*/s', '$1', $strip($logged)),
            'El contenido de la guía no debe variar por usuario (se cachea como página pública).'
        );
    }

    public function test_the_guide_is_linked_from_menu_footer_registration_and_the_backoffice(): void
    {
        $guide = route('publish.guide');

        $this->get('/')->assertOk()->assertSee('href="'.$guide.'"', false);
        $this->get('/register')->assertOk()->assertSee($guide, false);

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee($guide, false);
        $this->actingAs($user)->get(route('backend.my-entities.index'))->assertOk()->assertSee($guide, false);

        $admin = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $admin->assignRole('administrador');
        $this->actingAs($admin)->get(route('backend.mitos.create'))->assertOk()->assertSee($guide, false);
    }

    public function test_the_guide_is_in_the_static_sitemap(): void
    {
        $this->get(route('sitemap.static'))->assertOk()->assertSee('/como-publicar', false);
    }
}
