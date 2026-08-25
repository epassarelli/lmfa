<?php

namespace App\Support\Automation\Mfa\Repositories;

use App\Support\Automation\Mfa\BacklogTask;
use App\Support\Automation\Mfa\Contracts\BacklogRepository;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleSheetsBacklogRepository implements BacklogRepository
{
    /**
     * @param  array<string, string>  $columnMap
     */
    public function __construct(
        protected string $spreadsheetUrl,
        protected string $sheetName,
        protected string $readRange,
        protected array $columnMap = [],
    ) {
    }

    public function all(): array
    {
        $payload = $this->fetchValues($this->qualifiedRange($this->readRange));
        $rows = $payload['values'] ?? [];

        if (count($rows) < 2) {
            return [];
        }

        $header = array_map(static fn ($value) => (string) $value, $rows[0]);
        $tasks = [];

        foreach (array_slice($rows, 1) as $index => $rowValues) {
            $row = ['__row_number' => $index + 2];

            foreach ($header as $columnIndex => $columnName) {
                $row[$columnName] = (string) ($rowValues[$columnIndex] ?? '');
            }

            $tasks[] = new BacklogTask($index + 2, $row);
        }

        return $tasks;
    }

    public function claim(BacklogTask $task, array $context): array
    {
        return $this->mutateTask($task, [
            $this->column('delegation_state') => 'En curso',
            $this->column('closing_notes') => $this->appendNote($task->notes(), (string) ($context['note'] ?? '')),
        ], 'google_sheets');
    }

    public function markDone(BacklogTask $task, array $context): array
    {
        return $this->mutateTask($task, [
            $this->column('status') => 'Hecha',
            $this->column('delegation_state') => 'Cerrada',
            $this->column('closing_notes') => $this->appendNote($task->notes(), (string) ($context['note'] ?? '')),
        ], 'google_sheets');
    }

    public function markNeedsReview(BacklogTask $task, array $context): array
    {
        return $this->mutateTask($task, [
            $this->column('status') => 'En revisión',
            $this->column('delegation_state') => 'En revision',
            $this->column('closing_notes') => $this->appendNote($task->notes(), (string) ($context['note'] ?? '')),
        ], 'google_sheets');
    }

    public function markBlocked(BacklogTask $task, array $context): array
    {
        return $this->mutateTask($task, [
            $this->column('status') => 'Bloqueada',
            $this->column('delegation_state') => 'Bloqueada',
            $this->column('closing_notes') => $this->appendNote($task->notes(), (string) ($context['note'] ?? '')),
        ], 'google_sheets');
    }

    /**
     * @param  array<string, string>  $updates
     * @return array<string, mixed>
     */
    protected function mutateTask(BacklogTask $task, array $updates, string $driver): array
    {
        $responseData = [];

        foreach ($updates as $columnName => $value) {
            if ($columnName === '') {
                continue;
            }

            $responseData[] = $this->updateCell($task->rowNumber, $columnName, $value);
        }

        return [
            'driver' => $driver,
            'task_id' => $task->id(),
            'row_number' => $task->rowNumber,
            'updates' => $updates,
            'responses' => $responseData,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fetchValues(string $range): array
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->get(sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
                $this->spreadsheetId(),
                rawurlencode($range)
            ), [
                'majorDimension' => 'ROWS',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Google Sheets read failed: '.$response->status().' '.$response->body());
        }

        return $response->json();
    }

    /**
     * @return array<string, mixed>
     */
    protected function updateCell(int $rowNumber, string $columnName, string $value): array
    {
        $range = sprintf(
            '%s!%s%d',
            $this->sheetName,
            $this->columnLetter($columnName),
            $rowNumber
        );

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->withQueryParameters([
                'valueInputOption' => 'USER_ENTERED',
            ])
            ->put(sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
                $this->spreadsheetId(),
                rawurlencode($range)
            ), [
                'range' => $range,
                'majorDimension' => 'ROWS',
                'values' => [[$value]],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Google Sheets write failed: '.$response->status().' '.$response->body());
        }

        return $response->json();
    }

    protected function accessToken(): string
    {
        $clientId = env('MFA_GOOGLE_CLIENT_ID');
        $clientSecret = env('MFA_GOOGLE_CLIENT_SECRET');
        $refreshToken = env('MFA_GOOGLE_REFRESH_TOKEN');

        $missing = [];

        if (blank($clientId)) {
            $missing[] = 'MFA_GOOGLE_CLIENT_ID';
        }

        if (blank($clientSecret)) {
            $missing[] = 'MFA_GOOGLE_CLIENT_SECRET';
        }

        if (blank($refreshToken)) {
            $missing[] = 'MFA_GOOGLE_REFRESH_TOKEN';
        }

        if ($missing !== []) {
            throw new RuntimeException('Faltan variables de entorno OAuth para Google Sheets: '.implode(', ', $missing));
        }

        $response = Http::asForm()
            ->acceptJson()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Google OAuth refresh failed: '.$response->status().' '.$response->body());
        }

        $accessToken = (string) $response->json('access_token', '');

        if ($accessToken === '') {
            throw new RuntimeException('Google OAuth no devolvió access_token para el orquestador.');
        }

        return $accessToken;
    }

    protected function spreadsheetId(): string
    {
        if (preg_match('~/spreadsheets/d/([a-zA-Z0-9-_]+)~', $this->spreadsheetUrl, $matches) === 1) {
            return $matches[1];
        }

        throw new RuntimeException('No se pudo extraer el spreadsheet id desde la URL configurada.');
    }

    protected function qualifiedRange(string $range): string
    {
        return sprintf('%s!%s', $this->sheetName, $range);
    }

    protected function column(string $key): string
    {
        return (string) ($this->columnMap[$key] ?? '');
    }

    protected function columnLetter(string $columnName): string
    {
        $headers = array_values($this->columnMap);
        $index = array_search($columnName, $headers, true);

        if ($index === false) {
            throw new RuntimeException('No existe índice para la columna de Drive: '.$columnName);
        }

        return $this->toA1Column($index + 1);
    }

    protected function toA1Column(int $position): string
    {
        $column = '';

        while ($position > 0) {
            $position--;
            $column = chr(65 + ($position % 26)).$column;
            $position = intdiv($position, 26);
        }

        return $column;
    }

    protected function appendNote(string $current, string $note): string
    {
        $note = trim($note);

        if ($note === '') {
            return $current;
        }

        return trim($current."\n".$note);
    }
}
