<?php

namespace Tests\Feature\Penias;

use App\Models\PeniaProfile;
use App\Models\Provincia;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PeniaProfileAuditCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_penia_auditor_is_read_only_and_prioritizes_incomplete_profiles(): void
    {
        $province = Provincia::create(['nombre' => 'Provincia auditor '.uniqid()]);
        $profile = PeniaProfile::factory()->create([
            'title' => 'Peña incompleta auditor',
            'slug' => 'penia-incompleta-auditor',
            'body' => '<p>Contenido breve.</p>',
            'province_id' => $province->id,
            'city' => null,
            'excerpt' => null,
            'source_urls' => [],
            'verification_status' => 'pending',
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $profile->refresh();
        $before = $profile->getAttributes();

        $exitCode = Artisan::call('mfa:penias:audit', ['--published' => true, '--limit' => 10]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Peña incompleta auditor', $output);
        $this->assertStringContainsString('P1', $output);
        $this->assertSame($before, $profile->fresh()->getAttributes());
    }
}
