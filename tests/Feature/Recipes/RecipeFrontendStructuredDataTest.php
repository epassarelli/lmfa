<?php

namespace Tests\Feature\Recipes;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecipeFrontendStructuredDataTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function structured_recipe_emits_recipe_schema_with_only_persisted_data(): void
    {
        $user = User::factory()->create();

        DB::table('comidas')->insert([
            'titulo' => 'Locro estructurado',
            'slug' => 'locro-estructurado',
            'receta' => '<p>Contenido editorial de la receta.</p>',
            'excerpt' => 'Locro tradicional argentino.',
            'ingredients' => json_encode(['Maíz blanco', 'Porotos', 'Zapallo']),
            'instructions' => json_encode(['Remojar los granos.', 'Cocinar lentamente.']),
            'prep_time_minutes' => 30,
            'cook_time_minutes' => 180,
            'servings' => '8 porciones',
            'seo_title' => 'Locro argentino: receta tradicional',
            'meta_description' => 'Ingredientes y preparación del locro argentino.',
            'foto' => null,
            'publicar' => now(),
            'user_id' => $user->id,
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/recetas-de-comidas-tipicas-argentinas/locro-estructurado');

        $response->assertOk();
        $response->assertSee('"@type": "Recipe"', false);
        $response->assertSee('"recipeIngredient"', false);
        $response->assertSee('"prepTime": "PT30M"', false);
        $response->assertSee('Locro argentino: receta tradicional', false);
    }

    /** @test */
    public function legacy_recipe_without_structure_does_not_emit_recipe_schema(): void
    {
        $user = User::factory()->create();

        DB::table('comidas')->insert([
            'titulo' => 'Receta legacy',
            'slug' => 'receta-legacy',
            'receta' => '<p>Texto legacy sin estructura.</p>',
            'foto' => null,
            'publicar' => now(),
            'user_id' => $user->id,
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/recetas-de-comidas-tipicas-argentinas/receta-legacy');

        $response->assertOk();
        $response->assertDontSee('"@type": "Recipe"', false);
    }
}
