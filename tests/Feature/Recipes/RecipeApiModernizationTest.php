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

    /** @test */
    public function api_protects_recipe_author_and_visit_counter(): void
    {
        $admin = $this->admin();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/foods', [
            'titulo' => 'Receta protegida',
            'receta' => '<p>Contenido editorial.</p>',
            'user_id' => $otherUser->id,
            'visitas' => 5000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'visitas']);

        $validResponse = $this->postJson('/api/v1/foods', [
            'titulo' => 'Receta protegida',
            'receta' => '<p>Contenido editorial.</p>',
        ]);

        $validResponse->assertCreated()
            ->assertJsonPath('user_id', $admin->id)
            ->assertJsonPath('visitas', 0);
    }

    /** @test */
    public function admin_can_partially_update_structured_recipe_without_changing_protected_fields(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $recipe = Comida::create([
            'titulo' => 'Receta antes del refresh',
            'slug' => 'receta-antes-del-refresh',
            'receta' => '<p>Preparación anterior.</p>',
            'user_id' => $admin->id,
            'estado' => 0,
            'visitas' => 23,
        ]);

        $response = $this->putJson("/api/v1/foods/{$recipe->id}", [
            'receta' => '<h1>No debe persistir</h1><p>Preparación actualizada.</p>',
            'ingredients' => ['500 g de harina', '250 ml de agua'],
            'instructions' => ['Mezclar los ingredientes.', 'Cocinar a fuego lento.'],
            'prep_time_minutes' => 20,
            'cook_time_minutes' => 45,
            'region' => 'Cuyo',
        ]);

        $response->assertOk()
            ->assertJsonPath('id', $recipe->id)
            ->assertJsonPath('ingredients.0', '500 g de harina')
            ->assertJsonPath('instructions.1', 'Cocinar a fuego lento.')
            ->assertJsonPath('region', 'Cuyo')
            ->assertJsonPath('user_id', $admin->id)
            ->assertJsonPath('visitas', 23);

        $recipe->refresh();
        $this->assertStringNotContainsString('<h1', strtolower($recipe->receta));
        $this->assertSame(23, $recipe->visitas);
        $this->assertSame($admin->id, $recipe->user_id);
    }
}
