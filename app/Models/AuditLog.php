<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'action',
        'old_values_json',
        'new_values_json',
        'meta_json',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values_json' => 'array',
        'new_values_json' => 'array',
        'meta_json'       => 'array',
        'created_at'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quick static helper to log an action.
     */
    public static function log(
        string $entityType,
        int $entityId,
        string $action,
        array $oldValues = [],
        array $newValues = [],
        array $meta = []
    ): static {
        return static::create([
            'user_id'         => auth()->id(),
            'entity_type'     => $entityType,
            'entity_id'       => $entityId,
            'action'          => $action,
            'old_values_json' => $oldValues ?: null,
            'new_values_json' => $newValues ?: null,
            'meta_json'       => $meta ?: null,
            'ip'              => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);
    }
}
