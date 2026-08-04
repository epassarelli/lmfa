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
}
