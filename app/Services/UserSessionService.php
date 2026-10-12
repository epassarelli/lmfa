<?php

namespace App\Services;

use App\Models\User;
use App\Support\DeviceLabel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sesiones activas de una persona. Funciona con el driver `database` (valor por defecto del proyecto):
 * es el único que permite saber de quién es cada sesión.
 */
class UserSessionService
{
    public function enabled(): bool
    {
        return config('session.driver') === 'database';
    }

    /**
     * @return array<int, array{id:string, device:string, ip:?string, last_active:\Illuminate\Support\Carbon, current:bool}>
     */
    public function list(User $user, ?string $currentSessionId): array
    {
        if (! $this->enabled()) {
            return [];
        }

        return $this->table()
            ->where('user_id', $user->getKey())
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(fn ($row) => [
                'id' => (string) $row->id,
                'device' => DeviceLabel::from($row->user_agent),
                'ip' => $row->ip_address,
                'last_active' => Carbon::createFromTimestamp((int) $row->last_activity),
                'current' => $currentSessionId !== null && $row->id === $currentSessionId,
            ])
            ->all();
    }

    /**
     * Cierra todas las sesiones salvo la actual y rota el remember_token (la cookie "recordarme" de
     * otros dispositivos deja de servir). Devuelve cuántas sesiones se cerraron.
     */
    public function logoutOthers(User $user, ?string $currentSessionId): int
    {
        $user->forceFill(['remember_token' => Str::random(60)])->save();

        if (! $this->enabled()) {
            return 0;
        }

        return $this->table()
            ->where('user_id', $user->getKey())
            ->when($currentSessionId, fn ($query) => $query->where('id', '!=', $currentSessionId))
            ->delete();
    }

    /** Cierra absolutamente todas las sesiones (incluida la actual). */
    public function logoutAll(User $user): int
    {
        return $this->enabled() ? $this->table()->where('user_id', $user->getKey())->delete() : 0;
    }

    private function table()
    {
        return DB::connection(config('session.connection'))->table(config('session.table', 'sessions'));
    }
}
