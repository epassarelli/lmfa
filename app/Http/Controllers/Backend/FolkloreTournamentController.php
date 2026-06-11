<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\UpdateFolkloreTournamentRequest;
use App\Models\FolkloreTournament;
use App\Models\Interprete;
use App\Services\FolkloreTournamentFixtureService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FolkloreTournamentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tournaments = FolkloreTournament::query()
            ->withCount(['groups', 'participants', 'matches'])
            ->orderByDesc('year')
            ->orderBy('name')
            ->get();

        return view('backend.folklore_tournaments.index', compact('tournaments'));
    }

    public function show(FolkloreTournament $tournament)
    {
        $tournament->load([
            'groups.participants.artist',
            'matches' => fn ($query) => $query->with(['group', 'participant1', 'participant2', 'winner'])->orderBy('phase')->orderBy('matchday'),
        ]);

        return view('backend.folklore_tournaments.show', compact('tournament'));
    }

    public function edit(FolkloreTournament $tournament)
    {
        $tournament->load(['groups', 'participants.artist']);
        $groups = $tournament->groups()->orderBy('sort_order')->get();
        $participants = $tournament->participants()->with('artist')->orderBy('seed_order')->get();
        $interpretes = Interprete::query()
            ->where('estado', 1)
            ->orderBy('interprete')
            ->get(['id', 'interprete']);

        return view('backend.folklore_tournaments.edit', compact('tournament', 'groups', 'participants', 'interpretes'));
    }

    public function update(
        UpdateFolkloreTournamentRequest $request,
        FolkloreTournament $tournament,
        FolkloreTournamentFixtureService $fixtureService
    ) {
        $data = $request->validated();

        DB::transaction(function () use ($tournament, $data, $fixtureService) {
            $tournament->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'year' => $data['year'],
                'status' => $data['status'],
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'rules' => $data['rules'] ?? null,
            ]);

            foreach ($data['participant_groups'] as $participantId => $groupId) {
                $artist = Interprete::query()->findOrFail($data['participant_artists'][$participantId]);

                $tournament->participants()
                    ->whereKey($participantId)
                    ->update([
                        'group_id' => $groupId,
                        'artist_id' => $artist->id,
                        'display_name' => $artist->interprete,
                        'slug' => Str::slug($artist->interprete),
                    ]);
            }

            $fixtureService->rebuildGroupStage($tournament->fresh());
        });

        return redirect()
            ->route('backend.folklore-tournaments.show', $tournament)
            ->with('success', 'Torneo actualizado correctamente.');
    }

    public function matches(FolkloreTournament $tournament)
    {
        $matches = $tournament->matches()
            ->with(['group', 'participant1', 'participant2', 'winner'])
            ->orderBy('phase')
            ->orderBy('matchday')
            ->orderBy('id')
            ->get();

        return view('backend.folklore_tournaments.matches', compact('tournament', 'matches'));
    }
}
