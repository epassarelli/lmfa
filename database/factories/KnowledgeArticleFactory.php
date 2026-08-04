<?php

namespace Database\Factories;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KnowledgeArticleFactory extends Factory
{
    protected $model = KnowledgeArticle::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'knowledge_category_id' => KnowledgeCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->sentence(18),
            'body' => '<p>'.implode('</p><p>', $this->faker->paragraphs(4)).'</p>',
            'image_alt' => $this->faker->sentence(6),
            'seo_title' => $title,
            'meta_description' => $this->faker->sentence(20),
            'primary_keyword' => $this->faker->word(),
            'secondary_keywords' => implode(', ', $this->faker->words(3)),
            'editorial_status' => 'draft',
            'published_at' => null,
            'last_verified_at' => now(),
            'author_id' => User::factory(),
            'reviewed_by' => null,
            'visits' => 0,
        ];
    }

    public function published(): self
    {
        return $this->state(fn () => [
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }
}
