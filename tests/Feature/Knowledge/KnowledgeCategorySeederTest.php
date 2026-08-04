<?php

namespace Tests\Feature\Knowledge;

use App\Models\KnowledgeCategory;
use Database\Seeders\KnowledgeCategorySeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KnowledgeCategorySeederTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_seeds_the_initial_categories_idempotently(): void
    {
        $seeder = app(KnowledgeCategorySeeder::class);

        $seeder->run();
        $seeder->run();

        $this->assertSame(9, KnowledgeCategory::count());
        $this->assertDatabaseHas('knowledge_categories', ['slug' => 'cancionero']);
        $this->assertDatabaseHas('knowledge_categories', ['slug' => 'provincias']);
    }
}
