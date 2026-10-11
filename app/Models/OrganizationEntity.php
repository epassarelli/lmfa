<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrganizationEntity extends Model
{
    protected $table = 'organization_entities';

    protected $fillable = [
        'organization_id',
        'entity_type',
        'entity_id',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo('entity');
    }
}
