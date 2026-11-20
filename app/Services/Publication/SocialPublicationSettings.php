<?php

namespace App\Services\Publication;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * Ajustes operativos editables desde el panel (sin secretos): ejecutor activo, pausa, horarios por tipo y red,
 * email de avisos y estado de la última corrida de cada horario. Se guardan en un JSON fuera de git
 * (storage/app/private) para no requerir migraciones. Spec: social-publication-laravel-executor.
 */
class SocialPublicationSettings
{
    public const KINDS = ['news' => 'Noticias', 'events' => 'Eventos'];
    public const PROVIDERS = ['facebook' => 'Facebook', 'instagram' => 'Instagram'];
    public const EXECUTORS = ['n8n', 'laravel'];

    public function defaults(): array
    {
        return [
            'executor' => 'n8n',
            'paused' => false,
            'alert_email' => config('social_publication.alert_email'),
            'schedules' => [
                'news' => ['facebook' => ['enabled' => true, 'time' => '11:00'], 'instagram' => ['enabled' => true, 'time' => '11:00']],
                'events' => ['facebook' => ['enabled' => false, 'time' => '18:00'], 'instagram' => ['enabled' => false, 'time' => '18:00']],
            ],
            'last_runs' => [],
        ];
    }

    public function all(): array
    {
        $path = config('social_publication.settings_path');
        $stored = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        return array_replace_recursive($this->defaults(), is_array($stored) ? $stored : []);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all(), $key, $default);
    }

    public function executorIsLaravel(): bool
    {
        return $this->get('executor') === 'laravel';
    }

    public function paused(): bool
    {
        return (bool) $this->get('paused');
    }

    public function update(array $changes): array
    {
        $settings = array_replace_recursive($this->all(), $changes);
        $this->write($settings);
        return $settings;
    }

    /** Horarios que deben correr ahora: una vez por día, FB antes que IG, con ventana de tolerancia. */
    public function dueSlots(?Carbon $now = null): array
    {
        $now = ($now ?? now())->copy()->timezone(config('social_publication.timezone'));
        $settings = $this->all();
        $due = [];
        foreach (array_keys(self::KINDS) as $kind) {
            foreach (array_keys(self::PROVIDERS) as $provider) {
                $slot = $settings['schedules'][$kind][$provider] ?? null;
                if (!($slot['enabled'] ?? false) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $slot['time'] ?? '')) { continue; }
                $at = $now->copy()->setTimeFromTimeString($slot['time']);
                $window = $at->copy()->addMinutes(config('social_publication.schedule_window_minutes'));
                if ($now->lt($at) || $now->gte($window) || ($settings['last_runs'][$kind.'.'.$provider] ?? null) === $now->toDateString()) { continue; }
                $due[] = ['kind' => $kind, 'provider' => $provider, 'time' => $slot['time']];
            }
        }
        usort($due, fn ($a, $b) => [$a['time'], $a['provider'] === 'facebook' ? 0 : 1] <=> [$b['time'], $b['provider'] === 'facebook' ? 0 : 1]);
        return $due;
    }

    public function markRun(string $kind, string $provider, ?Carbon $now = null): void
    {
        $date = ($now ?? now())->copy()->timezone(config('social_publication.timezone'))->toDateString();
        $this->update(['last_runs' => [$kind.'.'.$provider => $date]]);
    }

    private function write(array $settings): void
    {
        $path = config('social_publication.settings_path');
        if (!is_dir(dirname($path))) { mkdir(dirname($path), 0750, true); }
        $tmp = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
        file_put_contents($tmp, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        rename($tmp, $path);
    }
}
