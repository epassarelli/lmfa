<?php

namespace App\Services;

use App\Models\FolkloreTournamentGroup;
use App\Models\FolkloreTournamentMatch;
use App\Models\FolkloreTournamentParticipant;
use Illuminate\Support\Collection;

class FolkloreTournamentStandingService
{
    public function calculateGroup(FolkloreTournamentGroup $group): Collection
    {
        $participants = $group->participants()->get()->keyBy('id');
        $standings = $participants->map(function (FolkloreTournamentParticipant $participant) {
            return [
                'participant_id' => $participant->id,
                'display_name' => $participant->display_name,
                'pj' => 0,
                'pg' => 0,
                'pe' => 0,
                'pp' => 0,
                'votos_a_favor' => 0,
                'votos_en_contra' => 0,
                'diferencia' => 0,
                'puntos' => 0,
                'manual_order' => null,
            ];
        });

        $matches = $group->matches()
            ->where('phase', FolkloreTournamentMatch::PHASE_GROUP)
            ->where('status', FolkloreTournamentMatch::STATUS_FINISHED)
            ->get();

        foreach ($matches as $match) {
            $this->applyMatchToStanding(
                $standings,
                $match->participant_1_id,
                $match->participant_1_votes,
                $match->participant_2_votes
            );

            $this->applyMatchToStanding(
                $standings,
                $match->participant_2_id,
                $match->participant_2_votes,
                $match->participant_1_votes
            );
        }

        return $standings
            ->map(function (array $row) {
                $row['diferencia'] = $row['votos_a_favor'] - $row['votos_en_contra'];

                return $row;
            })
            ->sort(function (array $a, array $b) {
                return
                    [$b['puntos'], $b['diferencia'], $b['votos_a_favor'], -$b['votos_en_contra'], -$b['participant_id']]
                    <=>
                    [$a['puntos'], $a['diferencia'], $a['votos_a_favor'], -$a['votos_en_contra'], -$a['participant_id']];
            })
            ->values();
    }

    private function applyMatchToStanding(Collection $standings, int $participantId, int $votesFor, int $votesAgainst): void
    {
        $row = $standings->get($participantId);

        if (!$row) {
            return;
        }

        $row['pj']++;
        $row['votos_a_favor'] += $votesFor;
        $row['votos_en_contra'] += $votesAgainst;

        if ($votesFor > $votesAgainst) {
            $row['pg']++;
            $row['puntos'] += 3;
        } elseif ($votesFor < $votesAgainst) {
            $row['pp']++;
        } else {
            $row['pe']++;
            $row['puntos']++;
        }

        $standings->put($participantId, $row);
    }
}
