<?php

namespace Tests\Feature\Artists;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArtistAuditCommandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function artist_auditor_is_read_only_and_prioritizes_poor_biographies(): void
    {
        $author = User::factory()->create();

        DB::table('interpretes')->insert([
            'interprete' => 'Artista Incompleto Auditor',
            'slug' => 'artista-incompleto-auditor',
            'biografia' => '<p>Bio breve.</p>',
            'user_id' => $author->id,
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $before = DB::table('interpretes')->where('slug', 'artista-incompleto-auditor')->first();

        $exitCode = Artisan::call('mfa:artists:audit', ['--active' => true, '--limit' => 10]);
        $output = Artisan::output();

        $after = DB::table('interpretes')->where('slug', 'artista-incompleto-auditor')->first();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Artista Incompleto Auditor', $output);
        $this->assertStringContainsString('P1', $output);
        $this->assertEquals($before, $after);
    }
}
