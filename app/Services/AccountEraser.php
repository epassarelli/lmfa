<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\EntityClaimRequest;
use App\Models\NewsletterSubscriber;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Eliminación de una cuenta por anonimización en el lugar (mismo criterio que el flujo de eliminación
 * de datos de Meta). Un borrado real arrastraría en cascada solicitudes, membresías y notificaciones y
 * dejaría la autoría del contenido en NULL o apuntando a un usuario inexistente.
 */
class AccountEraser
{
    public function __construct(private UserSessionService $sessions)
    {
    }

    /**
     * Reemplaza la identidad de la persona por valores anónimos (sin guardar: lo hace quien llama).
     */
    public function scrubIdentity(User $user): void
    {
        $user->forceFill([
            'name' => 'Usuario eliminado '.$user->getKey(),
            'email' => sprintf('deleted-user-%d-%s@mifolkloreargentino.invalid', $user->getKey(), Str::lower(Str::random(12))),
            'email_verified_at' => null,
            'password' => Hash::make(Str::random(40)),
            'phone' => null,
        ]);
    }

    /**
     * Borra los datos personales y los vínculos de la cuenta y la deja anonimizada. El contenido que la
     * persona cargó se conserva, sin su nombre.
     */
    public function erase(User $user): void
    {
        $id = $user->getKey();
        $originalEmail = $user->email;

        DB::transaction(function () use ($user, $id, $originalEmail) {
            EntityClaimRequest::query()->where('user_id', $id)->pending()->update(['status' => EntityClaimRequest::CANCELLED]);
            OrganizationMember::query()->where('user_id', $id)->delete();

            NewsletterSubscriber::query()->where('user_id', $id)->orWhere('email', $originalEmail)->delete();

            if (Schema::hasTable('contact_messages')) {
                ContactMessage::query()->where('user_id', $id)->orWhere('email', $originalEmail)->delete();
            }

            UserNotification::query()->where('user_id', $id)->delete();
            $user->socialAccounts()->delete();
            $user->tokens()->delete();
            $user->syncRoles([]);
            $user->syncPermissions([]);

            $this->scrubIdentity($user);
            $user->forceFill([
                'google_id' => null,
                'facebook_id' => null,
                'profile_photo_path' => null,
                'remember_token' => null,
                'last_login_at' => null,
                'is_verified_publisher' => false,
                'status' => 'deleted',
            ])->save();
        });

        $this->sessions->logoutAll($user);
        $user->forgetManagedEntities();
    }
}
