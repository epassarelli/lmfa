<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrowserRuntimeCleanlinessTest extends TestCase
{
    public function test_active_layouts_do_not_contain_debug_console_logs(): void
    {
        foreach ([
            resource_path('views/layouts/adminlte.blade.php'),
            resource_path('views/layouts/partials/select-interprete.blade.php'),
        ] as $template) {
            $this->assertStringNotContainsString('console.log', file_get_contents($template));
        }
    }
}
