<?php

namespace Tests\Feature\Recipes;

use App\Models\Comida;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RecipeApiModernizationTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');
        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    /** @test */
    public function admin_can_create_inactive_structured_recipe_without_legacy_required_fields(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/foods', [
            'titulo' => 'Empanadas de prueba',
            'receta' => '<h1>No debe persistir</h1><p>Contenido editorial de la receta.</p>',
            'excerpt' => 'Receta tradicional de prueba.',
            'ingredients' => ['500 g de harina', '250 g de carne', '1 cebolla'],
            'instructions' => ['Preparar el relleno.', 'Armar las empanadas.', 'Cocinar.'],
            'servings' => '12 unidades',
            'region' => 'Noroeste argentino',
            'seo_title' => 'Empanadas de prueba: receta tradicional',
            'meta_description' => 'Ingredientes y preparación de empanadas de prueba.',
            'image_alt' => 'Empanadas argentinas recién horneadas',
        ]);

        $response->assertCreated()
            ->assertJsonPath('slug', 'empanadas-de-prueba')
            ->assertJsonPath('estado', 0)
            ->assertJsonPath('visitas', 0)
            ->assertJsonPath('user_id', $admin->id);

        $recipe = Comida::where('slug', 'empanadas-de-prueba')->firstOrFail();

        $this->assertStringNotContainsString('<h1', strtolower($recipe->receta));
        $this->assertCount(3, $recipe->ingredients);
        $this->assertCount(3, $recipe->instructions);
    }

    /** @test */
    public function api_rejects_duplicate_recipe_slug(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        Comida::create([
            'titulo' => 'Receta existente',
            'slug' => 'receta-duplicada',
            'receta' => '<p>Contenido.</p>',
            'user_id' => $admin->id,
            'estado' => 0,
            'visitas' => 0,
        ]);

        $response = $this->postJson('/api/v1/foods', [
            'titulo' => 'Otra receta',
            'slug' => 'receta-duplicada',
            'receta' => '<p>Otro contenido.</p>',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['slug']);
    }
}
