<?php

namespace App\Support\Automation\Mfa\Repositories;

use App\Support\Automation\Mfa\Contracts\BacklogRepository;
use InvalidArgumentException;

class BacklogRepositoryFactory
{
    /**
     * @param  array<string, mixed>  $projectConfig
     */
    public function make(array $projectConfig, ?string $driver = null): BacklogRepository
    {
        $driver = $driver ?: ($projectConfig['execution']['default_driver'] ?? 'fixture');

        return match ($driver) {
            'fixture' => new FixtureBacklogRepository(base_path($projectConfig['drive']['snapshot_path'])),
            'google_sheets' => new GoogleSheetsBacklogRepository(
                $projectConfig['drive']['spreadsheet_url'],
                $projectConfig['drive']['sheet_name'],
                $projectConfig['drive']['read_range'],
                $projectConfig['drive']['columns'] ?? [],
            ),
            'codex_exec' => new CodexExecBacklogRepository($projectConfig['execution']['codex_command'] ?? 'codex exec'),
            default => throw new InvalidArgumentException('Driver de backlog no soportado: '.$driver),
        };
    }
}
