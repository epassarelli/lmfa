<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use App\Support\InitialsAvatar;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use \App\Traits\ManagesOrganizationEntities;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'facebook_id',
        'phone',
        'status',
        'is_verified_publisher',
        'publisher_type_default',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_verified_publisher' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        // 'profile_photo_url',
    ];


    /** Avatar de iniciales (data URI SVG), estable por usuario. */
    public function avatarUrl(): string
    {
        return InitialsAvatar::dataUri((string) $this->name, $this->getKey() ?? 0);
    }

    /** Nombre del rol más relevante, en lenguaje de usuario. */
    public function roleLabel(): string
    {
        return match (true) {
            $this->hasRole('administrador') => 'Administrador',
            $this->hasRole('verificado') => 'Colaborador verificado',
            $this->hasRole('colaborador') => 'Colaborador',
            default => 'Usuario',
        };
    }

    // --- Menú de usuario de AdminLTE (usermenu_image / usermenu_desc / usermenu_profile_url)

    public function adminlte_image(): string
    {
        return $this->avatarUrl();
    }

    public function adminlte_desc(): string
    {
        $since = $this->created_at ? ' · desde '.$this->created_at->copy()->locale('es')->translatedFormat('M Y') : '';

        return $this->roleLabel().$since;
    }

    public function adminlte_profile_url(): string
    {
        return route('backend.account.edit', [], false);
    }

    public function socialAccounts()
    {
        return $this->morphMany(SocialAccount::class, 'owner');
    }

    public function publicationRequests()
    {
        return $this->hasMany(PublicationRequest::class, 'requested_by');
    }

    /**
     * Helpers de rol
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('administrador');
    }

    public function canPublish(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        try {
            return $this->hasPermissionTo('publish contents');
        } catch (\Exception $e) {
            return false;
        }
    }
}
