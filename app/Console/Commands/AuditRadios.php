<?php

namespace App\Console\Commands;

use App\Models\RadioListeningChannel;
use App\Models\RadioProgram;
use App\Models\RadioSignal;
use App\Services\EditorialImageResolver;
use App\Support\SeoMetadata;
use Illuminate\Console\Command;

class AuditRadios extends Command
{
    protected $signature = 'mfa:radios:audit
        {--type=all : all, signals o programs}
        {--published : Audita sólo registros publicados}
        {--csv= : Guarda el detalle completo en CSV sin modificar la base}
        {--limit=25 : Cantidad de registros con peor score que se muestran}';

    protected $description = 'Audita señales y programas de radio en modo read-only y exporta su deuda editorial.';

    public function handle(EditorialImageResolver $imageResolver): int
    {
        $type = (string) $this->option('type');
        if (! in_array($type, ['all', 'signals', 'programs'], true)) {
            $this->error('El tipo debe ser all, signals o programs.');

            return self::INVALID;
        }

        $rows = [];
        if (in_array($type, ['all', 'signals'], true)) {
            $query = RadioSignal::query()->with(['provincia', 'listeningChannels'])->withCount('programs');
            if ($this->option('published')) {
                $query->publishedVisible();
            }
            $query->orderBy('id')->chunkById(200, function ($signals) use (&$rows, $imageResolver): void {
                foreach ($signals as $signal) {
                    $rows[] = $this->auditSignal($signal, $imageResolver);
                }
            });
        }

        if (in_array($type, ['all', 'programs'], true)) {
            $query = RadioProgram::query()->with(['signal.listeningChannels', 'slots']);
            if ($this->option('published')) {
                $query->publishedVisible();
            }
            $query->orderBy('id')->chunkById(200, function ($programs) use (&$rows): void {
                foreach ($programs as $program) {
                    $rows[] = $this->auditProgram($program);
                }
            });
        }

        usort($rows, fn (array $a, array $b): int => ($a['score'] <=> $b['score'])
            ?: ($b['visits'] <=> $a['visits'])
            ?: strcmp($a['entity'], $b['entity'])
            ?: ($a['id'] <=> $b['id']));

        $summary = ['P1' => 0, 'P2' => 0, 'P3' => 0];
        foreach ($rows as $row) {
            $summary[$row['priority']]++;
        }

        $this->table(['Total', 'Señales', 'Programas', 'P1', 'P2', 'P3'], [[
            count($rows),
            count(array_filter($rows, fn ($row) => $row['entity'] === 'signal')),
            count(array_filter($rows, fn ($row) => $row['entity'] === 'program')),
            $summary['P1'], $summary['P2'], $summary['P3'],
        ]]);
        $this->newLine();
        $this->info('Radios y programas con mayor deuda editorial');
        $this->table(
            ['Tipo', 'ID', 'Título', 'Score', 'Prioridad', 'Visitas', 'Palabras', 'Faltantes'],
            array_map(fn ($row) => [$row['entity'], $row['id'], $row['title'], $row['score'], $row['priority'], $row['visits'], $row['words'], implode(', ', $row['missing'])], array_slice($rows, 0, max(1, (int) $this->option('limit'))))
        );

        $this->writeCsv($rows, trim((string) $this->option('csv')));
        $this->newLine();
        $this->info('Auditoría completada en modo read-only: no se modificaron señales ni programas.');

        return self::SUCCESS;
    }

    private function auditSignal(RadioSignal $signal, EditorialImageResolver $imageResolver): array
    {
        [$score, $missing, $words] = $this->commonScore($signal);
        $channels = $signal->listeningChannels->where('is_active', true);
        $modes = $signal->transmission_modes ?? [];
        $airReady = ! in_array('air', $modes, true) || $channels->contains(fn (RadioListeningChannel $channel) => in_array($channel->frequency_band, ['AM', 'FM'], true) && filled($channel->frequency));
        $digitalReady = ! array_intersect($modes, ['web', 'streaming']) || $channels->contains(fn (RadioListeningChannel $channel) => filled($channel->url));

        if ($modes && $airReady && $digitalReady) {
            $score += 20;
        } else {
            $missing[] = 'canales_para_cada_medio';
        }
        if (! in_array('air', $modes, true) || ($signal->province_id && filled($signal->city))) {
            $score += 10;
        } else {
            $missing[] = 'ubicación_de_emisión';
        }
        if (filled($signal->editorial_focus) && filled($signal->coverage_scope)) {
            $score += 5;
        } else {
            $missing[] = 'foco/cobertura';
        }
        if (! $imageResolver->resolve($signal, false)->isFallback()) {
            $score += 5;
        } else {
            $missing[] = 'imagen';
        }
        if ($signal->programs_count > 0) {
            $score += 5;
        } else {
            $missing[] = 'programas_relacionados';
        }

        return $this->row('signal', $signal, $score, $words, $missing, [
            'signal' => null,
            'channels' => $channels->count(),
            'slots' => 0,
            'next_broadcast' => null,
        ]);
    }

    private function auditProgram(RadioProgram $program): array
    {
        [$score, $missing, $words] = $this->commonScore($program);
        $signalChannels = $program->signal?->listeningChannels?->where('is_active', true) ?? collect();
        $listenable = ($program->radio_signal_id && $signalChannels->contains(fn ($channel) => filled($channel->url) || filled($channel->frequency)))
            || (filled($program->platform) && filled($program->listening_url));
        if ($listenable) {
            $score += 20;
        } else {
            $missing[] = 'forma_de_escucha';
        }
        $activeSlots = $program->slots->where('is_active', true);
        if ($activeSlots->isNotEmpty()) {
            $score += 15;
        } else {
            $missing[] = 'grilla_semanal';
        }
        if ($program->is_folklore) {
            $score += 5;
        } else {
            $missing[] = 'foco_folklórico';
        }
        if ($program->radio_signal_id || filled($program->platform)) {
            $score += 5;
        } else {
            $missing[] = 'emisora/plataforma';
        }
        $next = $program->nextBroadcast();

        return $this->row('program', $program, $score, $words, $missing, [
            'signal' => $program->signal?->title,
            'channels' => $signalChannels->count(),
            'slots' => $activeSlots->count(),
            'next_broadcast' => $next ? $next['starts_at']->toIso8601String() : null,
        ]);
    }

    private function commonScore(RadioSignal|RadioProgram $record): array
    {
        $score = filled($record->title) && filled($record->slug) ? 10 : 0;
        $missing = $score ? [] : ['identidad'];
        if (filled($record->excerpt)) {
            $score += 5;
        } else {
            $missing[] = 'bajada';
        }
        $body = SeoMetadata::clean($record->body);
        $words = $body === '' ? 0 : count(preg_split('/\s+/u', $body, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        if ($words >= 300) {
            $score += 10;
        } elseif ($words >= 150) {
            $score += 5;
            $missing[] = 'cuerpo<300';
        } else {
            $missing[] = 'cuerpo<150';
        }
        if (! empty($record->source_urls)) {
            $score += 10;
        } else {
            $missing[] = 'fuentes';
        }
        if ($record->hasCurrentVerification()) {
            $score += 15;
        } else {
            $missing[] = 'verificación_vigente';
        }
        if (filled($record->seo_title) && filled($record->meta_description)) {
            $score += 10;
        } else {
            $missing[] = 'seo';
        }

        return [$score, $missing, $words];
    }

    private function row(string $entity, RadioSignal|RadioProgram $record, int $score, int $words, array $missing, array $extra): array
    {
        $score = min(100, $score);

        return array_merge([
            'entity' => $entity,
            'id' => $record->id,
            'title' => $record->title,
            'slug' => $record->slug,
            'score' => $score,
            'priority' => $score <= 50 ? 'P1' : ($score <= 75 ? 'P2' : 'P3'),
            'visits' => (int) $record->visits,
            'words' => $words,
            'editorial_status' => $record->editorial_status,
            'verification_status' => $record->verification_status,
            'last_verified_at' => $record->last_verified_at?->toIso8601String(),
            'missing' => array_values(array_unique($missing)),
        ], $extra);
    }

    private function writeCsv(array $rows, string $csvPath): void
    {
        if ($csvPath === '') {
            return;
        }
        $directory = dirname($csvPath);
        if ($directory !== '.' && ! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $csv = fopen($csvPath, 'wb');
        fputcsv($csv, ['entity', 'id', 'title', 'slug', 'score', 'priority', 'visits', 'words', 'editorial_status', 'verification_status', 'last_verified_at', 'signal', 'channels', 'slots', 'next_broadcast', 'missing']);
        foreach ($rows as $row) {
            fputcsv($csv, [$row['entity'], $row['id'], $row['title'], $row['slug'], $row['score'], $row['priority'], $row['visits'], $row['words'], $row['editorial_status'], $row['verification_status'], $row['last_verified_at'], $row['signal'], $row['channels'], $row['slots'], $row['next_broadcast'], implode('|', $row['missing'])]);
        }
        fclose($csv);
        $this->line('Detalle CSV: '.$csvPath);
    }
}
