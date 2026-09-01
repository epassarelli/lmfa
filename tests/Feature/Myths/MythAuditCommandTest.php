<?php

namespace Tests\Feature\Myths;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MythAuditCommandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function myth_auditor_is_read_only_and_prioritizes_poor_legacy_content(): void
    {
        $user = User::factory()->create();

        DB::table('mitos')->insert([
            'titulo' => 'Mito Incompleto Auditor',
            'slug' => 'mito-incompleto-auditor',
            'mito' => '<p>Texto breve.</p>',
            'foto' => null,
            'publicar' => now(),
            'user_id' => $user->id,
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $before = DB::table('mitos')->where('slug', 'mito-incompleto-auditor')->first();

        $exitCode = Artisan::call('mfa:myths:audit', ['--active' => true, '--limit' => 10]);
        $output = Artisan::output();

        $after = DB::table('mitos')->where('slug', 'mito-incompleto-auditor')->first();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Mito Incompleto Auditor', $output);
        $this->assertStringContainsString('P1', $output);
        $this->assertEquals($before, $after);
    }
}
