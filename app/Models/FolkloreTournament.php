<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FolkloreTournament extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'year',
        'starts_at',
        'ends_at',
        'status',
        'rules',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tournament) {
            if (blank($tournament->slug)) {
                $tournament->slug = Str::slug($tournament->name);
            }
        });
    }

    public function groups(): HasMany
    {
        return $this->hasMany(FolkloreTournamentGroup::class, 'tournament_id')->orderBy('sort_order');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(FolkloreTournamentParticipant::class, 'tournament_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(FolkloreTournamentMatch::class, 'tournament_id');
    }
}
