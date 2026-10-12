<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EntityClaimRequest extends Model
{
    protected static function booted(): void
    {
        $forget = fn () => app(\App\Services\AdminAttentionService::class)->forget();
        static::saved($forget);
        static::deleted($forget);
    }

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';

    public const KIND_LINK = 'link';
    public const KIND_CREATE = 'create';

    protected $fillable = [
        'user_id',
        'kind',
        'entity_type',
        'entity_id',
        'proposed_name',
        'evidence_url',
        'proposed_data',
        'relationship',
        'message',
        'contact_phone',
        'status',
        'organization_id',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'proposed_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo('entity');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function entityConfig(): ?array
    {
        return collect(config('entity_management.entities'))->firstWhere('model', $this->entity_type);
    }

    public function entityName(): ?string
    {
        $config = $this->entityConfig();

        return $config && $this->entity ? (string) $this->entity->{$config['name_column']} : null;
    }

    public function isCreation(): bool
    {
        return $this->kind === self::KIND_CREATE;
    }

    /**
     * Nombre a mostrar: la ficha vinculada o, si todavía no existe, el nombre propuesto.
     */
    public function displayName(): ?string
    {
        return $this->entityName() ?? $this->proposed_name;
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::PENDING);
    }

    public static function statusLabels(): array
    {
        return [
            self::PENDING => 'Pendiente',
            self::APPROVED => 'Aprobada',
            self::REJECTED => 'Rechazada',
            self::CANCELLED => 'Cancelada',
        ];
    }
}
