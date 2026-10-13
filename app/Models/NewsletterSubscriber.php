<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    public const PENDING = 'pending';
    public const ACTIVE = 'active';
    public const UNSUBSCRIBED = 'unsubscribed';
    public const BOUNCED = 'bounced';

    /** Vigencia del enlace de confirmación. */
    public const CONFIRMATION_DAYS = 7;

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'status',
        'token',
        'source',
        'consented_at',
        'confirmed_at',
        'consent_ip_hash',
        'last_confirmation_sent_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'last_confirmation_sent_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::random(40);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::ACTIVE);
    }

    /** Quien puede recibir envíos: activo Y con la suscripción confirmada desde su correo. */
    public function scopeDeliverable(Builder $query): Builder
    {
        return $query->where('status', self::ACTIVE)->whereNotNull('confirmed_at');
    }

    /** Todavía no confirmó: altas nuevas pendientes y suscriptores heredados activos sin confirmar. */
    public function scopeUnconfirmed(Builder $query): Builder
    {
        return $query->whereNull('confirmed_at')->whereIn('status', [self::PENDING, self::ACTIVE]);
    }

    public function isDeliverable(): bool
    {
        return $this->status === self::ACTIVE && $this->confirmed_at !== null;
    }

    public function isUnconfirmed(): bool
    {
        return $this->confirmed_at === null && in_array($this->status, [self::PENDING, self::ACTIVE], true);
    }

    /** Enlace firmado y con vencimiento para confirmar la suscripción. */
    public function confirmationUrl(): string
    {
        return URL::temporarySignedRoute('newsletter.confirm.show', now()->addDays(self::CONFIRMATION_DAYS), ['token' => $this->token]);
    }

    public function unsubscribeUrl(): string
    {
        return route('newsletter.unsubscribe', $this->token);
    }
}
