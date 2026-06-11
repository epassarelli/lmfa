<?php

namespace App\Services;

use App\Models\FolkloreTournament;
use App\Models\FolkloreTournamentGroup;
use App\Models\FolkloreTournamentMatch;
use App\Models\FolkloreTournamentParticipant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FolkloreTournamentFixtureService
{
    private const KNOCKOUT_PHASE_ORDER = [
        FolkloreTournamentMatch::PHASE_ROUND_16,
        FolkloreTournamentMatch::PHASE_QUARTER_FINAL,
        FolkloreTournamentMatch::PHASE_SEMI_FINAL,
        FolkloreTournamentMatch::PHASE_THIRD_PLACE,
        FolkloreTournamentMatch::PHASE_FINAL,
    ];

    public function generateGroupStage(FolkloreTournament $tournament): Collection
    {
        $groups = $tournament->groups()->with('participants')->get();

        return DB::transaction(function () use ($tournament, $groups) {
            $created = new Collection();

            foreach ($groups as $group) {
                $created = $created->merge($this->generateGroupMatches($tournament, $group));
            }

            $this->assignDailySchedule($tournament);

            return $created;
        });
    }

    public function rebuildGroupStage(FolkloreTournament $tournament): Collection
    {
        return DB::transaction(function () use ($tournament) {
            FolkloreTournamentMatch::query()
                ->where('tournament_id', $tournament->id)
                ->whereIn('phase', array_merge([FolkloreTournamentMatch::PHASE_GROUP], self::KNOCKOUT_PHASE_ORDER))
                ->delete();

            return $this->generateGroupStage($tournament->fresh());
        });
    }

    public function generateGroupMatches(FolkloreTournament $tournament, FolkloreTournamentGroup $group): Collection
    {
        $participants = $group->participants()->orderBy('seed_order')->get()->values();

        if ($participants->count() !== 4) {
            throw new RuntimeException("El grupo {$group->name} debe tener exactamente 4 participantes.");
        }

        $schedule = [
            [[$participants[0], $participants[3]], [$participants[1], $participants[2]]],
            [[$participants[0], $participants[2]], [$participants[3], $participants[1]]],
            [[$participants[0], $participants[1]], [$participants[2], $participants[3]]],
        ];

        $created = new Collection();

        foreach ($schedule as $matchday => $pairs) {
            foreach ($pairs as [$participant1, $participant2]) {
                $pair = [$participant1->id, $participant2->id];
                sort($pair);

                $exists = FolkloreTournamentMatch::query()
                    ->where('tournament_id', $tournament->id)
                    ->where('group_id', $group->id)
                    ->where('phase', FolkloreTournamentMatch::PHASE_GROUP)
                    ->where(function ($query) use ($pair) {
                        $query
                            ->where(function ($innerQuery) use ($pair) {
                                $innerQuery->where('participant_1_id', $pair[0])
                                    ->where('participant_2_id', $pair[1]);
                            })
                            ->orWhere(function ($innerQuery) use ($pair) {
                                $innerQuery->where('participant_1_id', $pair[1])
                                    ->where('participant_2_id', $pair[0]);
                            });
                    })
                    ->exists();

                if ($exists) {
                    continue;
                }

                $created->push(FolkloreTournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'group_id' => $group->id,
                    'phase' => FolkloreTournamentMatch::PHASE_GROUP,
                    'matchday' => $matchday + 1,
                    'participant_1_id' => $participant1->id,
                    'participant_2_id' => $participant2->id,
                    'status' => FolkloreTournamentMatch::STATUS_SCHEDULED,
                ]));
            }
        }

        return $created;
    }

    public function assignDailySchedule(FolkloreTournament $tournament, ?CarbonImmutable $startDate = null): void
    {
        $startDate ??= now()->addDay()->startOfDay()->toImmutable();

        $matches = FolkloreTournamentMatch::query()
            ->where('tournament_id', $tournament->id)
            ->where('phase', FolkloreTournamentMatch::PHASE_GROUP)
            ->orderBy('matchday')
            ->orderBy('group_id')
            ->orderBy('id')
            ->get();

        foreach ($matches as $index => $match) {
            $match->update([
                'scheduled_at' => $startDate->addDays($index)->setTime(12, 0),
            ]);
        }
    }

    public function syncKnockoutStage(FolkloreTournament $tournament): void
    {
        DB::transaction(function () use ($tournament) {
            $tournament = $tournament->fresh();

            if ($this->groupStageIsComplete($tournament)) {
                $this->generateRoundOf16($tournament);
            }

            $this->generateQuarterFinals($tournament->fresh());
            $this->generateSemiFinals($tournament->fresh());
            $this->generateFinalAndThirdPlace($tournament->fresh());
            $this->scheduleKnockoutMatches($tournament->fresh());
        });
    }

    private function groupStageIsComplete(FolkloreTournament $tournament): bool
    {
        $expectedGroups = $tournament->groups()->count();
        if ($expectedGroups === 0) {
            return false;
        }

        foreach ($tournament->groups()->withCount(['matches' => function ($query) {
            $query->where('phase', FolkloreTournamentMatch::PHASE_GROUP)
                ->where('status', FolkloreTournamentMatch::STATUS_FINISHED);
        }])->get() as $group) {
            if ($group->matches_count < 6) {
                return false;
            }
        }

        return true;
    }

    private function generateRoundOf16(FolkloreTournament $tournament): void
    {
        $existingCount = FolkloreTournamentMatch::query()
            ->where('tournament_id', $tournament->id)
            ->where('phase', FolkloreTournamentMatch::PHASE_ROUND_16)
            ->count();

        if ($existingCount > 0) {
            return;
        }

        $qualified = $this->qualifiedParticipantsByGroup($tournament);

        $pairings = [
            [$qualified['zona-a'][0], $qualified['zona-b'][1]],
            [$qualified['zona-c'][0], $qualified['zona-d'][1]],
            [$qualified['zona-e'][0], $qualified['zona-f'][1]],
            [$qualified['zona-g'][0], $qualified['zona-h'][1]],
            [$qualified['zona-b'][0], $qualified['zona-a'][1]],
            [$qualified['zona-d'][0], $qualified['zona-c'][1]],
            [$qualified['zona-f'][0], $qualified['zona-e'][1]],
            [$qualified['zona-h'][0], $qualified['zona-g'][1]],
        ];

        foreach ($pairings as $index => [$participant1, $participant2]) {
            $this->createKnockoutMatch(
                $tournament,
                FolkloreTournamentMatch::PHASE_ROUND_16,
                $index + 1,
                $participant1,
                $participant2
            );
        }
    }

    private function generateQuarterFinals(FolkloreTournament $tournament): void
    {
        if ($this->hasPhaseMatches($tournament, FolkloreTournamentMatch::PHASE_QUARTER_FINAL)) {
            return;
        }

        $winners = $this->phaseWinners($tournament, FolkloreTournamentMatch::PHASE_ROUND_16, 8);
        if ($winners === null) {
            return;
        }

        foreach (array_chunk($winners, 2) as $index => $participants) {
            $this->createKnockoutMatch(
                $tournament,
                FolkloreTournamentMatch::PHASE_QUARTER_FINAL,
                $index + 1,
                $participants[0],
                $participants[1]
            );
        }
    }

    private function generateSemiFinals(FolkloreTournament $tournament): void
    {
        if ($this->hasPhaseMatches($tournament, FolkloreTournamentMatch::PHASE_SEMI_FINAL)) {
            return;
        }

        $winners = $this->phaseWinners($tournament, FolkloreTournamentMatch::PHASE_QUARTER_FINAL, 4);
        if ($winners === null) {
            return;
        }

        foreach (array_chunk($winners, 2) as $index => $participants) {
            $this->createKnockoutMatch(
                $tournament,
                FolkloreTournamentMatch::PHASE_SEMI_FINAL,
                $index + 1,
                $participants[0],
                $participants[1]
            );
        }
    }

    private function generateFinalAndThirdPlace(FolkloreTournament $tournament): void
    {
        if (
            $this->hasPhaseMatches($tournament, FolkloreTournamentMatch::PHASE_FINAL)
            || $this->hasPhaseMatches($tournament, FolkloreTournamentMatch::PHASE_THIRD_PLACE)
        ) {
            return;
        }

        $semiFinals = FolkloreTournamentMatch::query()
            ->where('tournament_id', $tournament->id)
            ->where('phase', FolkloreTournamentMatch::PHASE_SEMI_FINAL)
            ->orderBy('matchday')
            ->orderBy('id')
            ->get();

        if ($semiFinals->count() !== 2 || $semiFinals->contains(fn (FolkloreTournamentMatch $match) => !$match->isFinished() || !$match->winner_participant_id)) {
            return;
        }

        $finalists = [];
        $thirdPlaceParticipants = [];

        foreach ($semiFinals as $match) {
            $finalists[] = $this->resolveParticipantById($match->winner_participant_id);

            $loserId = $match->winner_participant_id === $match->participant_1_id
                ? $match->participant_2_id
                : $match->participant_1_id;

            $thirdPlaceParticipants[] = $this->resolveParticipantById($loserId);
        }

        $this->createKnockoutMatch(
            $tournament,
            FolkloreTournamentMatch::PHASE_THIRD_PLACE,
            1,
            $thirdPlaceParticipants[0],
            $thirdPlaceParticipants[1]
        );

        $this->createKnockoutMatch(
            $tournament,
            FolkloreTournamentMatch::PHASE_FINAL,
            1,
            $finalists[0],
            $finalists[1]
        );
    }

    private function qualifiedParticipantsByGroup(FolkloreTournament $tournament): array
    {
        $standingService = app(FolkloreTournamentStandingService::class);
        $groups = $tournament->groups()->orderBy('sort_order')->get();
        $qualified = [];

        foreach ($groups as $group) {
            $standings = $standingService->calculateGroup($group);
            if ($standings->count() < 2) {
                throw new RuntimeException("La {$group->name} no tiene suficientes clasificados.");
            }

            $qualified[$group->slug ?: "group-{$group->id}"] = [
                $this->resolveParticipantById($standings[0]['participant_id']),
                $this->resolveParticipantById($standings[1]['participant_id']),
            ];
        }

        return $qualified;
    }

    private function phaseWinners(FolkloreTournament $tournament, string $phase, int $expectedMatches): ?array
    {
        $matches = FolkloreTournamentMatch::query()
            ->where('tournament_id', $tournament->id)
            ->where('phase', $phase)
            ->orderBy('matchday')
            ->orderBy('id')
            ->get();

        if ($matches->count() !== $expectedMatches) {
            return null;
        }

        $winners = [];

        foreach ($matches as $match) {
            if (!$match->isFinished() || !$match->winner_participant_id) {
                return null;
            }

            $winners[] = $this->resolveParticipantById($match->winner_participant_id);
        }

        return $winners;
    }

    private function createKnockoutMatch(
        FolkloreTournament $tournament,
        string $phase,
        int $matchday,
        FolkloreTournamentParticipant $participant1,
        FolkloreTournamentParticipant $participant2
    ): FolkloreTournamentMatch {
        return FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'phase' => $phase,
            'matchday' => $matchday,
            'participant_1_id' => $participant1->id,
            'participant_2_id' => $participant2->id,
            'status' => FolkloreTournamentMatch::STATUS_SCHEDULED,
        ]);
    }

    private function scheduleKnockoutMatches(FolkloreTournament $tournament): void
    {
        $lastGroupMatchDate = FolkloreTournamentMatch::query()
            ->where('tournament_id', $tournament->id)
            ->where('phase', FolkloreTournamentMatch::PHASE_GROUP)
            ->max('scheduled_at');

        $startDate = $lastGroupMatchDate
            ? CarbonImmutable::parse($lastGroupMatchDate)->addDay()->setTime(12, 0)
            : now()->addDay()->startOfDay()->setTime(12, 0)->toImmutable();

        $knockoutMatches = FolkloreTournamentMatch::query()
            ->where('tournament_id', $tournament->id)
            ->whereIn('phase', self::KNOCKOUT_PHASE_ORDER)
            ->orderByRaw("FIELD(phase, 'round_16', 'quarter_final', 'semi_final', 'third_place', 'final')")
            ->orderBy('matchday')
            ->orderBy('id')
            ->get();

        foreach ($knockoutMatches as $index => $match) {
            if ($match->scheduled_at) {
                continue;
            }

            $match->update([
                'scheduled_at' => $startDate->addDays($index),
            ]);
        }
    }

    private function hasPhaseMatches(FolkloreTournament $tournament, string $phase): bool
    {
        return FolkloreTournamentMatch::query()
            ->where('tournament_id', $tournament->id)
            ->where('phase', $phase)
            ->exists();
    }

    private function resolveParticipantById(int $participantId): FolkloreTournamentParticipant
    {
        return FolkloreTournamentParticipant::query()->findOrFail($participantId);
    }
}
