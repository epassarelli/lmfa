<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mensaje enviado desde el formulario de contacto. La tabla puede ser heredada (columnas en español
 * sin timestamps): `fecha_envio` hace de fecha de creación.
 */
class ContactMessage extends Model
{
    public const NEW = 'new';
    public const READ = 'read';
    public const ANSWERED = 'answered';
    public const SPAM = 'spam';

    protected $table = 'contact_messages';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'email',
        'asunto',
        'mensaje',
        'fecha_envio',
        'status',
        'user_id',
        'ip_hash',
        'read_at',
        'answered_at',
        'spam_reason',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'read_at' => 'datetime',
        'answered_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::NEW => 'Nuevo',
            self::READ => 'Leído',
            self::ANSWERED => 'Respondido',
            self::SPAM => 'Spam',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? (string) $this->status;
    }
}
