<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

/**
 * Registra el último ingreso (a lo sumo una vez cada 10 minutos, para no escribir en la base cada
 * vez que la sesión se recupera por "recordarme"). Actualización directa: no toca updated_at.
 */
class RecordLastLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $last = $user->last_login_at ?? null;

        if ($last && $last->gt(now()->subMinutes(10))) {
            return;
        }

        DB::table('users')->where('id', $user->getAuthIdentifier())->update(['last_login_at' => now()]);
    }
}
