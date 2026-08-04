<?php

namespace Tests\Feature\Knowledge;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KnowledgeAuthorizationTest extends TestCase
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
    public function admin_can_open_the_backend_knowledge_index(): void
    {
        $admin = $this->makeAdminUser();

        $response = $this->actingAs($admin)->get(route('backend.knowledge-articles.index'));

        $response->assertOk();
    }

    /** @test */
    public function regular_user_cannot_open_the_backend_knowledge_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('backend.knowledge-articles.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function regular_user_cannot_create_knowledge_articles_via_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = KnowledgeCategory::factory()->create();

        $response = $this->postJson('/api/v1/knowledge-articles', [
            'knowledge_category_id' => $category->id,
            'title' => 'Zamba carpera',
            'slug' => 'zamba-carpera',
            'body' => '<p>Contenido.</p>',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function article_author_without_admin_role_cannot_delete_other_articles_via_policy_protected_route(): void
    {
        $category = KnowledgeCategory::factory()->create();
        $author = User::factory()->create();
        $otherUser = User::factory()->create();

        $article = KnowledgeArticle::factory()->create([
            'knowledge_category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/v1/knowledge-articles/{$article->id}");

        $response->assertForbidden();
    }
}
