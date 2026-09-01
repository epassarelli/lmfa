<?php

namespace Tests\Feature\Myths;

use App\Models\Mito;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MythApiModernizationTest extends TestCase
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
    public function admin_can_create_inactive_myth_with_modern_editorial_fields(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/myths', [
            'titulo' => 'Leyenda de prueba',
            'content_type' => 'legend',
            'mito' => '<h1>No debe persistir</h1><p>Relato tradicional documentado.</p>',
            'excerpt' => 'Resumen editorial de la leyenda.',
            'region' => 'Noroeste argentino',
            'seo_title' => 'Leyenda de prueba: historia y tradición',
            'meta_description' => 'Relato y contexto cultural de una leyenda de prueba.',
            'image_alt' => 'Ilustración de la leyenda de prueba',
        ]);

        $response->assertCreated()
            ->assertJsonPath('slug', 'leyenda-de-prueba')
            ->assertJsonPath('content_type', 'legend')
            ->assertJsonPath('estado', 0)
            ->assertJsonPath('visitas', 0)
            ->assertJsonPath('user_id', $admin->id);

        $myth = Mito::where('slug', 'leyenda-de-prueba')->firstOrFail();
        $this->assertStringNotContainsString('<h1', strtolower($myth->mito));
    }

    /** @test */
    public function api_rejects_duplicate_myth_slug(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        Mito::create([
            'titulo' => 'Mito existente',
            'slug' => 'mito-duplicado',
            'mito' => '<p>Contenido.</p>',
            'user_id' => $admin->id,
            'estado' => 0,
            'visitas' => 0,
        ]);

        $response = $this->postJson('/api/v1/myths', [
            'titulo' => 'Otro mito',
            'slug' => 'mito-duplicado',
            'mito' => '<p>Otro contenido.</p>',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function api_protects_myth_author_and_visit_counter(): void
    {
        $admin = $this->admin();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/myths', [
            'titulo' => 'Mito protegido',
            'mito' => '<p>Relato editorial.</p>',
            'user_id' => $otherUser->id,
            'visitas' => 5000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'visitas']);

        $validResponse = $this->postJson('/api/v1/myths', [
            'titulo' => 'Mito protegido',
            'mito' => '<p>Relato editorial.</p>',
        ]);

        $validResponse->assertCreated()
            ->assertJsonPath('user_id', $admin->id)
            ->assertJsonPath('visitas', 0);
    }

    /** @test */
    public function admin_can_partially_update_myth_editorial_fields_without_changing_protected_fields(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $myth = Mito::create([
            'titulo' => 'Relato antes del refresh',
            'slug' => 'relato-antes-del-refresh',
            'mito' => '<p>Relato anterior.</p>',
            'content_type' => 'myth',
            'user_id' => $admin->id,
            'estado' => 0,
            'visitas' => 31,
        ]);

        $response = $this->putJson("/api/v1/myths/{$myth->id}", [
            'content_type' => 'legend',
            'mito' => '<h1>No debe persistir</h1><p>Relato actualizado por la bandeja editorial.</p>',
            'region' => 'Noroeste argentino',
            'seo_title' => 'Relato actualizado: historia y tradición',
        ]);

        $response->assertOk()
            ->assertJsonPath('id', $myth->id)
            ->assertJsonPath('content_type', 'legend')
            ->assertJsonPath('region', 'Noroeste argentino')
            ->assertJsonPath('user_id', $admin->id)
            ->assertJsonPath('visitas', 31);

        $myth->refresh();
        $this->assertStringNotContainsString('<h1', strtolower($myth->mito));
        $this->assertSame(31, $myth->visitas);
        $this->assertSame($admin->id, $myth->user_id);
    }
}
