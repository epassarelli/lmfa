<?php

namespace Tests\Feature\Recipes;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicRecipesFrontendTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function recipe_detail_uses_clean_metadata_keeps_canonical_and_does_not_emit_incomplete_recipe_schema(): void
    {
        DB::table('comidas')->insert([
            'id' => 80001,
            'titulo' => 'Locro Patrio',
            'slug' => 'locro-patrio',
            'receta' => '<p class="receta-bajada">Un plato tradicional para fechas patrias.</p><section class="receta-ingredientes"><h2>Ingredientes</h2><ul><li>Maiz blanco</li><li>Zapallo</li></ul></section><section class="receta-preparacion"><h2>Preparacion paso a paso</h2><ol><li>Remojar el maiz.</li><li>Cocinar a fuego bajo.</li></ol></section>',
            'foto' => 'locro-patrio.jpg',
            'visitas' => 5,
            'estado' => 1,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDay(),
        ]);

        DB::table('comidas')->insert([
            'id' => 80002,
            'titulo' => 'Lentejas criollas',
            'slug' => 'lentejas-criollas',
            'receta' => '<p>Otra receta valida.</p>',
            'foto' => 'lentejas-criollas.jpg',
            'visitas' => 3,
            'estado' => 1,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->call('GET', '/recetas-de-comidas-tipicas-argentinas/locro-patrio', [], [], [], $this->serverVariables());

        $response->assertOk();
        $response->assertSee('<title>Receta de Locro Patrio | Comida Tipica del Folklore</title>', false);
        $response->assertSee('<meta name="description" content="Un plato tradicional para fechas patrias. Ingredientes Maiz blanco Zapallo Preparacion paso a paso Remojar el maiz. Cocinar a fuego bajo.">', false);
        $response->assertSee('<link rel="canonical" href="https://mifolkloreargentino.com/recetas-de-comidas-tipicas-argentinas/locro-patrio" />', false);
        $response->assertSee('src="http://localhost/storage/comidas/locro-patrio.jpg"', false);
        $response->assertSee('alt="Receta de Locro Patrio"', false);
        $response->assertSee('class="prose receta-contenido max-w-none mb-4"', false);
        $response->assertDontSee('"@type": "Recipe"', false);
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    /** @test */
    public function unpublished_recipe_detail_returns_404(): void
    {
        DB::table('comidas')->insert([
            'titulo' => 'Receta privada',
            'slug' => 'receta-privada',
            'receta' => '<p>No deberia verse.</p>',
            'foto' => 'receta-privada.jpg',
            'visitas' => 0,
            'estado' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->call('GET', '/recetas-de-comidas-tipicas-argentinas/receta-privada', [], [], [], $this->serverVariables());

        $response->assertNotFound();
    }

    /** @test */
    public function recipe_letter_page_only_lists_published_recipes_and_keeps_canonical(): void
    {
        DB::table('comidas')->insert([
            'titulo' => 'Chacarera de olla',
            'slug' => 'chacarera-de-olla',
            'receta' => '<p>Publicado.</p>',
            'foto' => 'chacarera-de-olla.jpg',
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('comidas')->insert([
            'titulo' => 'Charquican reservado',
            'slug' => 'charquican-reservado',
            'receta' => '<p>Borrador.</p>',
            'foto' => 'charquican-reservado.jpg',
            'visitas' => 0,
            'estado' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->call('GET', '/recetas-de-comidas-tipicas-argentinas/letra/c', [], [], [], $this->serverVariables());

        $response->assertOk();
        $response->assertSee('Chacarera de olla');
        $response->assertDontSee('Charquican reservado');
        $response->assertSee('<link rel="canonical" href="https://mifolkloreargentino.com/recetas-de-comidas-tipicas-argentinas/letra/c" />', false);
    }

    /** @test */
    public function sitemap_includes_recipe_letter_pages_with_published_content(): void
    {
        DB::table('comidas')->insert([
            'titulo' => 'Empanadas saltenas',
            'slug' => 'empanadas-saltenas',
            'receta' => '<p>Publicado.</p>',
            'foto' => 'empanadas-saltenas.jpg',
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->call('GET', '/sitemap-estaticas.xml', [], [], [], $this->serverVariables());

        $response->assertOk();
        $response->assertSee('https://mifolkloreargentino.com/recetas-de-comidas-tipicas-argentinas/letra/e', false);
    }

    private function serverVariables(): array
    {
        return [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ];
    }
}
