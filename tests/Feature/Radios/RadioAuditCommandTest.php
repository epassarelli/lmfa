<?php

namespace Tests\Feature\Radios;

use App\Models\RadioProgram;
use App\Models\RadioSignal;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RadioAuditCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_radio_auditor_is_read_only_and_exports_signals_and_programs(): void
    {
        $signal = RadioSignal::factory()->create([
            'title' => 'Radio incompleta auditor', 'slug' => 'radio-incompleta-auditor',
            'excerpt' => null, 'body' => '<p>Breve.</p>', 'source_urls' => [],
            'seo_title' => null, 'meta_description' => null,
        ]);
        $program = RadioProgram::factory()->create([
            'title' => 'Programa incompleto auditor', 'slug' => 'programa-incompleto-auditor',
            'excerpt' => null, 'body' => '<p>Breve.</p>', 'source_urls' => [],
            'seo_title' => null, 'meta_description' => null,
        ]);
        $signal->refresh();
        $program->refresh();
        $signalBefore = $signal->getAttributes();
        $programBefore = $program->getAttributes();
        $csvPath = tempnam(sys_get_temp_dir(), 'mfa-radios-audit-');

        $exitCode = Artisan::call('mfa:radios:audit', ['--csv' => $csvPath, '--limit' => 10]);
        $output = Artisan::output();
        $csv = file_get_contents($csvPath);
        unlink($csvPath);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Radio incompleta auditor', $output);
        $this->assertStringContainsString('Programa incompleto auditor', $output);
        $this->assertStringContainsString('P1', $output);
        $this->assertStringContainsString('signal', $csv);
        $this->assertStringContainsString('program', $csv);
        $this->assertSame($signalBefore, $signal->fresh()->getAttributes());
        $this->assertSame($programBefore, $program->fresh()->getAttributes());
    }

    public function test_the_auditor_rejects_an_unknown_entity_type(): void
    {
        $this->assertSame(2, Artisan::call('mfa:radios:audit', ['--type' => 'otro']));
    }
}
