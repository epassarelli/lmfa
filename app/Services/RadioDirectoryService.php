<?php

namespace App\Services;

use App\Models\RadioListeningChannel;
use App\Models\RadioProgram;
use App\Models\RadioProgramSlot;
use App\Models\RadioSignal;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RadioDirectoryService
{
    public function publishSignal(RadioSignal $signal): RadioSignal
    {
        return $this->saveSignal($signal, ['editorial_status' => 'published']);
    }

    public function unpublishSignal(RadioSignal $signal): RadioSignal
    {
        return $this->saveSignal($signal, ['editorial_status' => 'draft', 'published_at' => null]);
    }

    public function saveSignal(RadioSignal $signal, array $data): RadioSignal
    {
        return DB::transaction(function () use ($signal, $data) {
            $payload = $this->normalize($signal, $data);
            $signal->fill(Arr::except($payload, ['channels']));
            $signal->save();

            if (array_key_exists('channels', $payload)) {
                $this->syncChannels($signal, $payload['channels']);
            }

            if ($signal->editorial_status === 'published') {
                $signal->load('listeningChannels');
                $this->ensureSignalPublishable($signal);
                $signal->published_at ??= now();
                $signal->save();
            }

            return $signal;
        });
    }

    public function publishProgram(RadioProgram $program): RadioProgram
    {
        return $this->saveProgram($program, ['editorial_status' => 'published']);
    }

    public function unpublishProgram(RadioProgram $program): RadioProgram
    {
        return $this->saveProgram($program, ['editorial_status' => 'draft', 'published_at' => null]);
    }

    public function saveProgram(RadioProgram $program, array $data): RadioProgram
    {
        return DB::transaction(function () use ($program, $data) {
            $payload = $this->normalize($program, $data);
            $program->fill(Arr::except($payload, ['slots']));
            $program->save();

            if (array_key_exists('slots', $payload)) {
                $this->syncSlots($program, $payload['slots']);
            }

            if ($program->editorial_status === 'published') {
                $program->load('signal.listeningChannels');
                $this->ensureProgramPublishable($program);
                $program->published_at ??= now();
                $program->save();
            }

            return $program;
        });
    }

    private function normalize(RadioSignal|RadioProgram $record, array $data): array
    {
        $title = $data['title'] ?? $record->title;

        if (filled($title) && blank($data['slug'] ?? $record->slug)) {
            $data['slug'] = Str::slug($title);
        }

        if (array_key_exists('body', $data)) {
            $data['body'] = RichTextHeadingSanitizer::normalize($data['body']);
        }

        if (array_key_exists('source_urls', $data)) {
            $data['source_urls'] = array_values(array_filter($data['source_urls'] ?? []));
        }

        return $data;
    }

    private function syncChannels(RadioSignal $signal, array $channels): void
    {
        $ids = [];
        $primaryAssigned = false;

        foreach ($channels as $order => $channel) {
            if (blank($channel['label'] ?? null) || blank($channel['channel_type'] ?? null)) {
                continue;
            }

            $model = isset($channel['id'])
                ? $signal->listeningChannels()->findOrFail($channel['id'])
                : new RadioListeningChannel();

            $isPrimary = ! $primaryAssigned && (bool) ($channel['is_primary'] ?? false);
            $primaryAssigned = $primaryAssigned || $isPrimary;

            $model->fill([
                'label' => $channel['label'],
                'channel_type' => $channel['channel_type'],
                'platform' => $channel['platform'] ?? null,
                'frequency_band' => $channel['frequency_band'] ?? null,
                'frequency' => $channel['frequency'] ?? null,
                'url' => $channel['url'] ?? null,
                'is_primary' => $isPrimary,
                'is_active' => (bool) ($channel['is_active'] ?? true),
                'sort_order' => $order,
            ]);
            $signal->listeningChannels()->save($model);
            $ids[] = $model->id;
        }

        $signal->listeningChannels()->whereNotIn('id', $ids)->delete();

        if (! $primaryAssigned && $ids) {
            $signal->listeningChannels()
                ->whereIn('id', $ids)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first()?->update(['is_primary' => true]);
        }
    }

    private function syncSlots(RadioProgram $program, array $slots): void
    {
        $ids = [];

        foreach ($slots as $slot) {
            if (! isset($slot['weekday'], $slot['starts_at'])) {
                continue;
            }

            $model = isset($slot['id'])
                ? $program->slots()->findOrFail($slot['id'])
                : new RadioProgramSlot();

            $model->fill([
                'weekday' => $slot['weekday'],
                'starts_at' => $slot['starts_at'],
                'ends_at' => $slot['ends_at'] ?? null,
                'timezone' => $slot['timezone'] ?? 'America/Argentina/Buenos_Aires',
                'is_active' => (bool) ($slot['is_active'] ?? true),
            ]);
            $program->slots()->save($model);
            $ids[] = $model->id;
        }

        $program->slots()->whereNotIn('id', $ids)->delete();
    }

    private function ensureSignalPublishable(RadioSignal $signal): void
    {
        $missing = $this->missingEditorialFields($signal);
        $modes = $signal->transmission_modes ?? [];
        $activeChannels = $signal->listeningChannels->where('is_active', true);

        if (empty($modes)) {
            $missing[] = 'tipo_de_transmision';
        }

        if (in_array('air', $modes, true)) {
            if (blank($signal->province_id) || blank($signal->city)) {
                $missing[] = 'ubicacion_de_la_senal_por_aire';
            }

            if (! $activeChannels->contains(fn (RadioListeningChannel $channel) => in_array($channel->frequency_band, ['AM', 'FM'], true) && filled($channel->frequency))) {
                $missing[] = 'frecuencia_am_o_fm';
            }
        }

        if (array_intersect($modes, ['web', 'streaming'])) {
            if (! $activeChannels->contains(fn (RadioListeningChannel $channel) => filled($channel->url))) {
                $missing[] = 'canal_digital_activo';
            }
        }

        if (! array_intersect($modes, ['air', 'web', 'streaming'])) {
            $missing[] = 'medio_de_escucha';
        }

        $this->throwIfMissing($missing, 'señal de radio');
    }

    private function ensureProgramPublishable(RadioProgram $program): void
    {
        $missing = $this->missingEditorialFields($program);

        if (! $program->is_folklore) {
            $missing[] = 'programa_de_folklore';
        }

        if ($program->radio_signal_id === null && (blank($program->platform) || blank($program->listening_url))) {
            $missing[] = 'plataforma_y_enlace_del_programa_independiente';
        }

        if ($program->radio_signal_id !== null && ! $program->signal?->listeningChannels->where('is_active', true)->contains(fn (RadioListeningChannel $channel) => filled($channel->url) || filled($channel->frequency))) {
            $missing[] = 'senal_con_canal_de_escucha_activo';
        }

        $this->throwIfMissing($missing, 'programa de radio');
    }

    private function missingEditorialFields(RadioSignal|RadioProgram $record): array
    {
        $missing = [];

        foreach (['title', 'body', 'verification_method', 'verified_by_user_id'] as $field) {
            if (blank($record->{$field})) {
                $missing[] = $field;
            }
        }

        if (empty($record->source_urls)) {
            $missing[] = 'source_urls';
        }

        if (! $record->hasCurrentVerification()) {
            $missing[] = 'current_verification';
        }

        return $missing;
    }

    private function throwIfMissing(array $missing, string $entity): void
    {
        if ($missing) {
            throw ValidationException::withMessages([
                'editorial_status' => ['No se puede publicar esta '.$entity.' sin los datos requeridos: '.implode(', ', array_unique($missing)).'.'],
            ]);
        }
    }
}
