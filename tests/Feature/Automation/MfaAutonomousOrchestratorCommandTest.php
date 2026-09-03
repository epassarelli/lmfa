<?php

namespace Tests\Feature\Automation;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MfaAutonomousOrchestratorCommandTest extends TestCase
{
    protected string $fixturePath;

    protected string $seedPath;

    protected string $fixtureBackup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturePath = base_path('project/automation/orchestrator/fixtures/mfa_backlog_snapshot.json');
        $this->seedPath = base_path('tests/Fixtures/mfa_backlog_snapshot.seed.json');
        $this->fixtureBackup = file_get_contents($this->seedPath);
        if (! is_dir(dirname($this->fixturePath))) {
            mkdir(dirname($this->fixturePath), 0777, true);
        }
        file_put_contents($this->fixturePath, $this->fixtureBackup);
    }

    protected function tearDown(): void
    {
        file_put_contents($this->fixturePath, $this->fixtureBackup);
        @unlink($this->fixturePath);

        parent::tearDown();
    }

    /** @test */
    public function dry_run_selects_the_highest_priority_autonomous_task_without_mutating_fixture(): void
    {
        $exitCode = Artisan::call('mfa:orchestrate-backlog', [
            '--project' => 'mfa',
            '--driver' => 'fixture',
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $output = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('completed', $output['status']);
        $this->assertSame('BL-0011F', $output['task']['id']);
        $this->assertSame('IA_AUTONOMA', $output['task']['autonomy_label']);
        $this->assertTrue($output['review_invocation']['independent_process']);
        $this->assertStringContainsString('mfa:review-run', $output['review_invocation']['command']);

        $fixtureAfter = file_get_contents($this->fixturePath);
        $this->assertSame($this->fixtureBackup, $fixtureAfter);
    }

    /** @test */
    public function fixture_run_marks_autonomous_inventory_task_as_done(): void
    {
        $exitCode = Artisan::call('mfa:orchestrate-backlog', [
            '--project' => 'mfa',
            '--driver' => 'fixture',
        ]);

        $this->assertSame(0, $exitCode);

        $output = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('completed', $output['status']);
        $this->assertSame('approved', $output['review']['status']);
        $this->assertSame('BL-0011F', $output['task']['id']);
        $this->assertContains('project/docs/08_inventario_tecnico_legacy.md', $output['execution']['touched_files']);
        $this->assertTrue($output['review_invocation']['independent_process']);
        $this->assertStringContainsString('mfa:review-run', $output['review_invocation']['command']);

        $fixture = json_decode(file_get_contents($this->fixturePath), true, 512, JSON_THROW_ON_ERROR);
        $rows = $fixture['rows'];
        $target = collect($rows)->firstWhere(0, 'BL-0011F');

        $this->assertSame('Hecha', $target[20]);
        $this->assertSame('Cerrada', $target[11]);
        $this->assertStringContainsString('Run', $target[27]);
    }

    /** @test */
    public function codex_exec_driver_reports_host_only_block_when_running_outside_windows_host(): void
    {
        $exitCode = Artisan::call('mfa:orchestrate-backlog', [
            '--project' => 'mfa',
            '--driver' => 'codex_exec',
        ]);

        $this->assertSame(1, $exitCode);

        $output = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('blocked', $output['status']);
        $this->assertStringContainsString('host Windows', $output['reason']);
    }

    /** @test */
    public function google_sheets_driver_reports_missing_oauth_credentials_without_faking_live_access(): void
    {
        putenv('MFA_GOOGLE_CLIENT_ID=');
        putenv('MFA_GOOGLE_CLIENT_SECRET=');
        putenv('MFA_GOOGLE_REFRESH_TOKEN=');
        unset($_ENV['MFA_GOOGLE_CLIENT_ID'], $_ENV['MFA_GOOGLE_CLIENT_SECRET'], $_ENV['MFA_GOOGLE_REFRESH_TOKEN']);
        unset($_SERVER['MFA_GOOGLE_CLIENT_ID'], $_SERVER['MFA_GOOGLE_CLIENT_SECRET'], $_SERVER['MFA_GOOGLE_REFRESH_TOKEN']);

        $exitCode = Artisan::call('mfa:orchestrate-backlog', [
            '--project' => 'mfa',
            '--driver' => 'google_sheets',
            '--dry-run' => true,
        ]);

        $this->assertSame(1, $exitCode);

        $output = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('blocked', $output['status']);
        $this->assertStringContainsString('Faltan variables de entorno OAuth', $output['reason']);
        $this->assertStringContainsString('MFA_GOOGLE_CLIENT_ID', $output['reason']);
        $this->assertStringContainsString('MFA_GOOGLE_REFRESH_TOKEN', $output['reason']);
    }
}
