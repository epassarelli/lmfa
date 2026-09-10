<?php

namespace Tests\Feature\Festivals;

use App\Models\User;
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
        $author = User::factory()->create();
        $monthId = DB::table('meses')->where('nombre', 'Enero')->value('id')
            ?: DB::table('meses')->insertGetId([
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
            [
                'title' => 'Festival Menos Visitado Auditor',
                'slug' => 'festival-menos-visitado-auditor',
                'body' => '<p>Contenido breve.</p>',
                'province_id' => $provinceId,
                'mes_id' => $monthId,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'user_id' => $author->id,
                'visitas' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Festival Más Visitado Auditor',
                'slug' => 'festival-mas-visitado-auditor',
                'body' => '<p>Contenido breve.</p>',
                'province_id' => $provinceId,
                'mes_id' => $monthId,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'user_id' => $author->id,
                'visitas' => 125,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $before = DB::table('festivales')
            ->whereIn('slug', ['festival-menos-visitado-auditor', 'festival-mas-visitado-auditor'])
            ->orderBy('id')
            ->get();

        $csvPath = tempnam(sys_get_temp_dir(), 'festival-audit-');

        try {
            $exitCode = Artisan::call('mfa:festivals:audit', [
                '--published' => true,
                '--limit' => 5,
                '--csv' => $csvPath,
            ]);

            $output = Artisan::output();
            $csv = array_map('str_getcsv', file($csvPath, FILE_IGNORE_NEW_LINES));
        } finally {
            @unlink($csvPath);
        }

        $after = DB::table('festivales')
            ->whereIn('slug', ['festival-menos-visitado-auditor', 'festival-mas-visitado-auditor'])
            ->orderBy('id')
            ->get();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Festival Más Visitado Auditor', $output);
        $this->assertStringContainsString('125', $output);
        $this->assertStringContainsString('P1', $output);
        $this->assertLessThan(
            strpos($output, 'Festival Menos Visitado Auditor'),
            strpos($output, 'Festival Más Visitado Auditor')
        );
        $this->assertContains('visits', $csv[0]);
        $visitsColumn = array_search('visits', $csv[0], true);
        $this->assertSame('125', $csv[1][$visitsColumn]);
        $this->assertSame('5', $csv[2][$visitsColumn]);
        $this->assertEquals($before, $after);
    }
}
