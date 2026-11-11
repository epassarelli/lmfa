<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReengagementService;

/** Baja de los mails de reactivación. Las rutas exigen URL firmada: el enlace del mail es la autorización. */
class ReengagementUnsubscribeController extends Controller
{
    public function show(User $user)
    {
        return view('frontend.reengagement.unsubscribe', ['user' => $user, 'done' => false]);
    }

    /** POST: también lo usan los clientes de correo con «baja de un clic» (sin token CSRF). */
    public function perform(User $user, ReengagementService $service)
    {
        $service->optOut($user);

        return view('frontend.reengagement.unsubscribe', ['user' => $user, 'done' => true]);
    }
}
