<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FolkloreTournamentGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'name',
        'slug',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $group) {
            if (blank($group->slug)) {
                $group->slug = Str::slug($group->name);
            }
        });
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(FolkloreTournament::class, 'tournament_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(FolkloreTournamentParticipant::class, 'group_id')->orderBy('seed_order');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(FolkloreTournamentMatch::class, 'group_id')->orderBy('matchday');
    }
}
