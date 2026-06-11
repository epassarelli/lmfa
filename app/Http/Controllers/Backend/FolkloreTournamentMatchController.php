<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\UpdateFolkloreTournamentMatchRequest;
use App\Models\FolkloreTournamentMatch;
use App\Services\FolkloreTournamentFixtureService;

class FolkloreTournamentMatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit(FolkloreTournamentMatch $match)
    {
        $match->load(['tournament.participants', 'group', 'participant1', 'participant2', 'winner']);

        $winnerOptions = collect([$match->participant1, $match->participant2])->filter();
        $participantOptions = $match->tournament->participants()->orderBy('display_name')->get();

        return view('backend.folklore_tournament_matches.edit', compact('match', 'winnerOptions', 'participantOptions'));
    }

    public function update(
        UpdateFolkloreTournamentMatchRequest $request,
        FolkloreTournamentMatch $match,
        FolkloreTournamentFixtureService $fixtureService
    )
    {
        $data = $request->validated();

        $participantIds = [
            (int) $data['participant_1_id'],
            (int) $data['participant_2_id'],
        ];

        if (!in_array((int) ($data['winner_participant_id'] ?? 0), $participantIds, true)) {
            $data['winner_participant_id'] = null;
        }

        if (($data['participant_1_votes'] ?? 0) !== ($data['participant_2_votes'] ?? 0) && empty($data['winner_participant_id'])) {
            $data['winner_participant_id'] = (int) $data['participant_1_votes'] > (int) $data['participant_2_votes']
                ? $data['participant_1_id']
                : $data['participant_2_id'];
        }

        if (($data['participant_1_votes'] ?? 0) === ($data['participant_2_votes'] ?? 0) && ($data['status'] ?? null) !== FolkloreTournamentMatch::STATUS_FINISHED) {
            $data['winner_participant_id'] = null;
        }

        $match->update($data);
        $fixtureService->syncKnockoutStage($match->tournament()->firstOrFail());

        return redirect()
            ->route('backend.folklore-tournaments.matches', $match->tournament_id)
            ->with('success', 'Partido actualizado correctamente.');
    }
}
