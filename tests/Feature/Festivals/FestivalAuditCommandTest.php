<?php

namespace Tests\Feature\Festivals;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FestivalAuditCommandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function festival_auditor_is_read_only_and_prioritizes_incomplete_records(): void
    {
        DB::table('meses')->insert([
            'id' => 1,
            'nombre' => 'Enero',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $provinceId = DB::table('provincias')->insertGetId([
            'nombre' => 'Provincia Auditor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('festivales')->insert([
            'title' => 'Festival Incompleto Auditor',
            'slug' => 'festival-incompleto-auditor',
            'body' => '<p>Contenido breve.</p>',
            'province_id' => $provinceId,
            'mes_id' => 1,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => 1,
            'visitas' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $before = DB::table('festivales')
            ->where('slug', 'festival-incompleto-auditor')
            ->first();

        $exitCode = Artisan::call('mfa:festivals:audit', [
            '--published' => true,
            '--limit' => 5,
        ]);

        $output = Artisan::output();

        $after = DB::table('festivales')
            ->where('slug', 'festival-incompleto-auditor')
            ->first();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Festival Incompleto Auditor', $output);
        $this->assertStringContainsString('P1', $output);
        $this->assertEquals($before, $after);
    }
}
