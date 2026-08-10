<?php

namespace Tests\Feature\Knowledge;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KnowledgeArticleApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeAdminUser(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    /** @test */
    public function admin_can_create_and_publish_a_knowledge_article_via_api(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $category = KnowledgeCategory::factory()->create(['slug' => 'ritmos']);

        $createResponse = $this->postJson('/api/v1/knowledge-articles', [
            'knowledge_category_id' => $category->id,
            'title' => 'Chacarera',
            'slug' => 'chacarera',
            'excerpt' => 'Una guía breve.',
            'body' => '<p>Contenido.</p>',
            'editorial_status' => 'draft',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.slug', 'chacarera')
            ->assertJsonPath('data.editorial_status', 'draft');

        $article = KnowledgeArticle::where('slug', 'chacarera')->firstOrFail();

        $publishResponse = $this->postJson("/api/v1/knowledge-articles/{$article->id}/publish");

        $publishResponse->assertOk()
            ->assertJsonPath('data.editorial_status', 'published');
    }

    /** @test */
    public function api_rejects_duplicate_slug_within_the_same_category(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $category = KnowledgeCategory::factory()->create();

        KnowledgeArticle::factory()->create([
            'knowledge_category_id' => $category->id,
            'slug' => 'zamba',
        ]);

        $response = $this->postJson('/api/v1/knowledge-articles', [
            'knowledge_category_id' => $category->id,
            'title' => 'Otra Zamba',
            'slug' => 'zamba',
            'body' => '<p>Contenido.</p>',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function api_can_create_an_article_using_a_category_slug_instead_of_a_numeric_id(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $category = KnowledgeCategory::factory()->create([
            'name' => 'Familia API Unica',
            'slug' => 'familia-api-unica',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/knowledge-articles', [
            'knowledge_category_slug' => 'familia-api-unica',
            'title' => 'Gato cuyano',
            'slug' => 'gato-cuyano',
            'body' => '<p>Contenido.</p>',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.category.slug', 'familia-api-unica');

        $this->assertDatabaseHas('knowledge_articles', [
            'slug' => 'gato-cuyano',
            'knowledge_category_id' => $category->id,
        ]);
    }

    /** @test */
    public function api_rejects_a_missing_evergreen_category_with_a_structured_blocked_code(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/knowledge-articles', [
            'title' => 'Articulo sin categoria',
            'slug' => 'articulo-sin-categoria',
            'body' => '<p>Contenido.</p>',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'BLOQUEADO_CATEGORIA')
            ->assertJsonValidationErrors(['knowledge_category_id']);
    }

    /** @test */
    public function api_rejects_a_non_existent_evergreen_category_slug_with_a_structured_blocked_code(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/knowledge-articles', [
            'knowledge_category_slug' => 'familia-inexistente',
            'title' => 'Articulo con familia invalida',
            'slug' => 'articulo-con-familia-invalida',
            'body' => '<p>Contenido.</p>',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'BLOQUEADO_CATEGORIA')
            ->assertJsonValidationErrors(['knowledge_category_id']);
    }

    /** @test */
    public function api_rejects_an_inactive_evergreen_category_even_if_the_identifier_exists(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $category = KnowledgeCategory::factory()->create([
            'slug' => 'tradiciones',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/knowledge-articles', [
            'knowledge_category_id' => $category->id,
            'title' => 'Articulo bloqueado',
            'slug' => 'articulo-bloqueado',
            'body' => '<p>Contenido.</p>',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'BLOQUEADO_CATEGORIA')
            ->assertJsonValidationErrors(['knowledge_category_id']);
    }

    /** @test */
    public function categories_endpoint_returns_the_active_catalog_with_stable_identifiers(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        KnowledgeCategory::factory()->create([
            'name' => 'Historia',
            'slug' => 'historia',
            'is_active' => true,
        ]);

        KnowledgeCategory::factory()->create([
            'name' => 'Oculta',
            'slug' => 'oculta',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/knowledge-categories');

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Historia',
                'slug' => 'historia',
                'is_active' => true,
            ])
            ->assertJsonMissing([
                'slug' => 'oculta',
            ]);
    }
}
