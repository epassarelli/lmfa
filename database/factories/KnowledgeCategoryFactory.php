<?php

namespace Database\Factories;

use App\Models\KnowledgeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KnowledgeCategoryFactory extends Factory
{
    protected $model = KnowledgeCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(1, 99),
            'is_active' => true,
            'seo_title' => $this->faker->sentence(4),
            'meta_description' => $this->faker->sentence(12),
        ];
    }
}
