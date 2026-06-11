<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FolkloreTournamentParticipant extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ELIMINATED = 'eliminated';
    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'tournament_id',
        'group_id',
        'artist_id',
        'display_name',
        'slug',
        'image_path',
        'seed_order',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $participant) {
            if (blank($participant->slug)) {
                $participant->slug = Str::slug($participant->display_name);
            }
        });
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(FolkloreTournament::class, 'tournament_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(FolkloreTournamentGroup::class, 'group_id');
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Interprete::class, 'artist_id');
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(FolkloreTournamentMatch::class, 'participant_1_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(FolkloreTournamentMatch::class, 'participant_2_id');
    }

    public function imageUrl(): string
    {
        if ($this->image_path && File::exists(public_path('storage/' . ltrim($this->image_path, '/')))) {
            return asset('storage/' . ltrim($this->image_path, '/'));
        }

        if ($this->artist?->foto && File::exists(public_path('storage/interpretes/' . $this->artist->foto))) {
            return asset('storage/interpretes/' . $this->artist->foto);
        }

        return asset('img/album.jpg');
    }
}
