<?php

namespace Tests\Feature\Directories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DirectoryFeatureFlagsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_both_public_directories_are_dark_by_default(): void
    {
        config([
            'features.penia_directory' => false,
            'features.radio_directory' => false,
        ]);

        $this->get('/penias')->assertNotFound();
        $this->get('/radios-de-folklore-argentino')->assertNotFound();
        $this->get('/sitemap-penias.xml')->assertNotFound();
        $this->get('/sitemap-radios.xml')->assertNotFound();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('/sitemap-penias.xml', false)
            ->assertDontSee('/sitemap-radios.xml', false);

        $this->get('/sitemap-estaticas.xml')
            ->assertOk()
            ->assertDontSee('/penias', false)
            ->assertDontSee('/radios-de-folklore-argentino', false);
    }

    public function test_each_directory_can_be_enabled_independently(): void
    {
        config([
            'features.penia_directory' => true,
            'features.radio_directory' => false,
        ]);

        $this->get('/penias')->assertOk();
        $this->get('/radios-de-folklore-argentino')->assertNotFound();
        $this->get('/sitemap-penias.xml')->assertOk();
        $this->get('/sitemap-radios.xml')->assertNotFound();
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/sitemap-penias.xml', false)
            ->assertDontSee('/sitemap-radios.xml', false);

        config([
            'features.penia_directory' => false,
            'features.radio_directory' => true,
        ]);

        $this->get('/penias')->assertNotFound();
        $this->get('/radios-de-folklore-argentino')->assertOk();
        $this->get('/sitemap-penias.xml')->assertNotFound();
        $this->get('/sitemap-radios.xml')->assertOk();
    }
}
