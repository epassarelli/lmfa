<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Una edición del newsletter: se arma como borrador, se revisa, se aprueba y recién entonces se envía. */
class NewsletterEdition extends Model
{
    public const DRAFT = 'draft';
    public const APPROVED = 'approved';
    public const SENT = 'sent';

    /** Tipos de contenido que puede llevar una edición, con su título en el correo. */
    public const SECTIONS = [
        'news' => 'Últimas noticias',
        'events' => 'Agenda: próximos eventos',
        'albums' => 'Discos nuevos',
        'songs' => 'Canciones nuevas',
        'artists' => 'Artistas nuevos',
    ];

    protected $fillable = [
        'edition_key', 'subject', 'intro', 'status', 'period_start', 'period_end', 'selection', 'snapshot',
        'scheduled_at', 'approved_by', 'approved_at', 'test_sent_at', 'sent_at', 'recipients_count', 'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'selection' => 'array',
        'snapshot' => 'array',
        'scheduled_at' => 'datetime',
        'approved_at' => 'datetime',
        'test_sent_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class, 'edition_key', 'edition_key');
    }

    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    public function isSent(): bool
    {
        return $this->status === self::SENT;
    }

    public function statusLabel(): string
    {
        return ['draft' => 'Borrador', 'approved' => 'Aprobada', 'sent' => 'Enviada'][$this->status] ?? $this->status;
    }

    public function period(): string
    {
        return $this->period_start->format('d/m').' al '.$this->period_end->format('d/m/Y');
    }
}
