<?php

namespace Tests\Feature\Api;

use App\Models\Categoria;
use App\Models\KnowledgeCategory;
use App\Models\Mes;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RichTextHeadingApiNormalizationTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAdminUser(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    private function category(): Categoria
    {
        return Categoria::first() ?: Categoria::create([
            'nombre' => 'General',
            'slug' => 'general',
        ]);
    }

    private function province(): Provincia
    {
        return Provincia::first() ?: Provincia::create([
            'nombre' => 'Cordoba',
        ]);
    }

    private function month(): Mes
    {
        return Mes::first() ?: Mes::create([
            'nombre' => 'Enero',
        ]);
    }

    /** @test */
    public function api_normalizes_h1_for_all_supported_editorial_entities(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/news', [
            'title' => 'News api headings',
            'slug' => 'news-api-headings',
            'body' => '<h1>News</h1><p>Texto.</p>',
            'categoria_id' => $this->category()->id,
        ])->assertCreated();
        $this->assertDatabaseHas('news', [
            'slug' => 'news-api-headings',
            'body' => '<h2>News</h2><p>Texto.</p>',
        ]);

        $this->postJson('/api/v1/events', [
            'title' => 'Event api headings',
            'body' => '<h1>Event</h1><p>Texto.</p>',
            'start_at' => now()->addDay()->toDateTimeString(),
        ])->assertCreated();
        $this->assertDatabaseHas('events', [
            'title' => 'Event api headings',
            'body' => '<h2>Event</h2><p>Texto.</p>',
        ]);

        $this->postJson('/api/v1/festivals', [
            'title' => 'Festival api headings',
            'body' => '<h1>Festival</h1><p>Texto.</p>',
            'province_id' => $this->province()->id,
            'mes_id' => $this->month()->id,
            'user_id' => $admin->id,
            'status' => 'draft',
        ])->assertCreated();
        $this->assertDatabaseHas('festivales', [
            'title' => 'Festival api headings',
            'body' => '<h2>Festival</h2><p>Texto.</p>',
        ]);

        $this->postJson('/api/v1/artists', [
            'interprete' => 'Artist api headings',
            'biografia' => '<h1>Bio</h1><p>Texto.</p>',
        ])->assertCreated();
        $this->assertDatabaseHas('interpretes', [
            'interprete' => 'Artist api headings',
            'biografia' => '<h2>Bio</h2><p>Texto.</p>',
        ]);

        $this->postJson('/api/v1/myths', [
            'titulo' => 'Myth api headings',
            'mito' => '<h1>Mito</h1><p>Texto.</p>',
            'foto' => 'myth-api-headings.jpg',
            'estado' => 1,
            'publicar' => now()->toDateTimeString(),
        ])->assertCreated();
        $this->assertDatabaseHas('mitos', [
            'titulo' => 'Myth api headings',
            'mito' => '<h2>Mito</h2><p>Texto.</p>',
        ]);

        $this->postJson('/api/v1/foods', [
            'titulo' => 'Food api headings',
            'receta' => '<h1>Receta</h1><p>Texto.</p>',
            'foto' => 'food-api-headings.jpg',
            'estado' => 1,
            'publicar' => now()->toDateTimeString(),
        ])->assertCreated();
        $this->assertDatabaseHas('comidas', [
            'titulo' => 'Food api headings',
            'receta' => '<h2>Receta</h2><p>Texto.</p>',
        ]);

        $knowledgeCategory = KnowledgeCategory::factory()->create([
            'name' => 'Historia',
            'slug' => 'historia',
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/knowledge-articles', [
            'knowledge_category_id' => $knowledgeCategory->id,
            'title' => 'Knowledge api headings',
            'slug' => 'knowledge-api-headings',
            'body' => '<h1>Knowledge</h1><p>Texto.</p>',
        ])->assertCreated()
            ->assertJsonPath('data.body', '<h2>Knowledge</h2><p>Texto.</p>');
    }
}
