<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolkloreTournamentMatch extends Model
{
    use HasFactory;

    public const PHASE_GROUP = 'group';
    public const PHASE_ROUND_16 = 'round_16';
    public const PHASE_QUARTER_FINAL = 'quarter_final';
    public const PHASE_SEMI_FINAL = 'semi_final';
    public const PHASE_THIRD_PLACE = 'third_place';
    public const PHASE_FINAL = 'final';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_VOTING_OPEN = 'voting_open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tournament_id',
        'group_id',
        'phase',
        'matchday',
        'participant_1_id',
        'participant_2_id',
        'participant_1_votes',
        'participant_2_votes',
        'winner_participant_id',
        'status',
        'scheduled_at',
        'voting_opens_at',
        'voting_closes_at',
        'instagram_url',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'voting_opens_at' => 'datetime',
        'voting_closes_at' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(FolkloreTournament::class, 'tournament_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(FolkloreTournamentGroup::class, 'group_id');
    }

    public function participant1(): BelongsTo
    {
        return $this->belongsTo(FolkloreTournamentParticipant::class, 'participant_1_id');
    }

    public function participant2(): BelongsTo
    {
        return $this->belongsTo(FolkloreTournamentParticipant::class, 'participant_2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(FolkloreTournamentParticipant::class, 'winner_participant_id');
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'Pendiente',
            self::STATUS_VOTING_OPEN => 'Votacion abierta',
            self::STATUS_CLOSED => 'Cerrado',
            self::STATUS_FINISHED => 'Finalizado',
            self::STATUS_CANCELLED => 'Cancelado',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function phaseLabel(): string
    {
        return match ($this->phase) {
            self::PHASE_GROUP => 'Fase de grupos',
            self::PHASE_ROUND_16 => 'Octavos de final',
            self::PHASE_QUARTER_FINAL => 'Cuartos de final',
            self::PHASE_SEMI_FINAL => 'Semifinal',
            self::PHASE_THIRD_PLACE => 'Tercer puesto',
            self::PHASE_FINAL => 'Final',
            default => ucfirst(str_replace('_', ' ', (string) $this->phase)),
        };
    }
}
