<?php

namespace Tests\Feature\Recipes;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecipeQualityAuditCommandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function quality_auditor_is_read_only_and_prioritizes_poor_legacy_content(): void
    {
        DB::table('comidas')->insert([
            'titulo' => 'Receta Incompleta Auditor',
            'slug' => 'receta-incompleta-auditor',
            'receta' => '<p>Texto breve.</p>',
            'foto' => null,
            'publicar' => now(),
            'user_id' => 1,
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $before = DB::table('comidas')->where('slug', 'receta-incompleta-auditor')->first();

        $exitCode = Artisan::call('mfa:recipes:audit', ['--active' => true, '--limit' => 10]);
        $output = Artisan::output();

        $after = DB::table('comidas')->where('slug', 'receta-incompleta-auditor')->first();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Receta Incompleta Auditor', $output);
        $this->assertStringContainsString('P1', $output);
        $this->assertEquals($before, $after);
    }
}
