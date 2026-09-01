<?php

namespace Tests\Feature\Recipes;

use App\Models\Comida;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RecipeBackofficeModernizationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function admin_can_keep_recipe_inactive_when_editing(): void
    {
        Role::findOrCreate('administrador', 'web');

        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $this->actingAs($admin);

        $recipe = Comida::create([
            'titulo' => 'Receta en revisión',
            'slug' => 'receta-en-revision',
            'receta' => '<p>Contenido inicial.</p>',
            'user_id' => $admin->id,
            'visitas' => 0,
            'estado' => 1,
        ]);

        $response = $this->put(route('backend.comidas.update', $recipe), [
            'titulo' => 'Receta en revisión',
            'slug' => 'receta-en-revision',
            'receta' => '<p>Contenido actualizado.</p>',
            'estado' => 0,
        ]);

        $response->assertRedirect(route('backend.comidas.index'));
        $this->assertSame(0, $recipe->fresh()->estado);
    }
}
