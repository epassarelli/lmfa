<?php

namespace Tests\Feature\Knowledge;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KnowledgeFrontendVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function published_article_is_visible_in_its_canonical_url(): void
    {
        $category = KnowledgeCategory::factory()->create(['slug' => 'historia']);
        $author = User::factory()->create();

        $article = KnowledgeArticle::factory()->published()->create([
            'knowledge_category_id' => $category->id,
            'title' => 'Boom del folklore argentino',
            'slug' => 'boom-del-folklore-argentino',
            'author_id' => $author->id,
        ]);

        $response = $this->get(route('enciclopedia.show', [
            'categorySlug' => $category->slug,
            'articleSlug' => $article->slug,
        ]));

        $response->assertOk()
            ->assertSee('Boom del folklore argentino');
    }

    /** @test */
    public function draft_article_is_not_visible_publicly(): void
    {
        $category = KnowledgeCategory::factory()->create(['slug' => 'aprender']);

        $article = KnowledgeArticle::factory()->create([
            'knowledge_category_id' => $category->id,
            'slug' => 'como-acompanar-una-chacarera',
            'editorial_status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->get(route('enciclopedia.show', [
            'categorySlug' => $category->slug,
            'articleSlug' => $article->slug,
        ]));

        $response->assertNotFound();
    }
}
