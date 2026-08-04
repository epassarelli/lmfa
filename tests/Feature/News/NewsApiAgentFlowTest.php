<?php

namespace Tests\Feature\News;

use App\Models\Categoria;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NewsApiAgentFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeAdminUser(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    protected function category(): Categoria
    {
        return Categoria::first() ?: Categoria::create([
            'nombre' => 'General',
            'slug' => 'general',
        ]);
    }

    /** @test */
    public function admin_api_create_news_defaults_to_draft_when_editorial_status_is_omitted(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/news', [
            'title' => 'Nueva noticia por agente',
            'slug' => 'nueva-noticia-por-agente',
            'body' => 'Contenido de prueba para carga automatica.',
            'categoria_id' => $this->category()->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('editorial_status', 'draft');

        $news = News::where('slug', 'nueva-noticia-por-agente')->firstOrFail();

        $this->assertSame('draft', $news->editorial_status);
        $this->assertNull($news->published_at);
    }

    /** @test */
    public function admin_api_create_news_accepts_explicit_draft_status(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/news', [
            'title' => 'Noticia draft explicita',
            'slug' => 'noticia-draft-explicita',
            'body' => 'Contenido de prueba para draft explicito.',
            'categoria_id' => $this->category()->id,
            'editorial_status' => 'draft',
        ]);

        $response->assertCreated()
            ->assertJsonPath('editorial_status', 'draft');
    }

    /** @test */
    public function non_admin_cannot_create_news_via_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/news', [
            'title' => 'Noticia bloqueada',
            'slug' => 'noticia-bloqueada',
            'body' => 'Contenido.',
            'categoria_id' => $this->category()->id,
        ]);

        $response->assertForbidden();
    }
}
