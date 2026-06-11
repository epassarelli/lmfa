<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FolkloreTournament;
use App\Models\FolkloreTournamentMatch;
use App\Services\FolkloreTournamentStandingService;

class FolkloreTournamentController extends Controller
{
    public function __construct(private readonly FolkloreTournamentStandingService $standingService)
    {
    }

    public function index()
    {
        $tournament = $this->loadTournament();
        $upcomingMatches = $tournament->matches
            ->whereIn('status', [
                FolkloreTournamentMatch::STATUS_SCHEDULED,
                FolkloreTournamentMatch::STATUS_VOTING_OPEN,
                FolkloreTournamentMatch::STATUS_CLOSED,
            ])
            ->sortBy([
                ['matchday', 'asc'],
                ['id', 'asc'],
            ])
            ->take(6)
            ->values();

        $latestResults = $tournament->matches
            ->where('status', FolkloreTournamentMatch::STATUS_FINISHED)
            ->sortByDesc('updated_at')
            ->take(6)
            ->values();

        $metaTitle = 'Copa del Folklore Argentino 2026';
        $metaDescription = 'Fixture, participantes, resultados, tablas y llaves de la Copa del Folklore Argentino 2026 en Mi Folklore Argentino.';
        $breadcrumbs = [
            ['label' => 'Copa del Folklore Argentino 2026'],
        ];

        return view('frontend.folklore_tournaments.index', compact(
            'tournament',
            'upcomingMatches',
            'latestResults',
            'metaTitle',
            'metaDescription',
            'breadcrumbs'
        ));
    }

    public function participants()
    {
        $tournament = $this->loadTournament();
        $participants = $tournament->participants->sortBy('display_name')->values();
        $metaTitle = 'Participantes - Copa del Folklore Argentino 2026';
        $metaDescription = 'Conoce a los 32 participantes de la Copa del Folklore Argentino 2026.';
        $breadcrumbs = $this->breadcrumbs('Participantes');

        return view('frontend.folklore_tournaments.participants', compact('tournament', 'participants', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function fixture()
    {
        $tournament = $this->loadTournament();
        $matchesByMatchday = $tournament->matches
            ->sortBy([
                ['phase', 'asc'],
                ['matchday', 'asc'],
                ['id', 'asc'],
            ])
            ->groupBy(function (FolkloreTournamentMatch $match) {
                return $match->phase . '|' . ($match->matchday ?? 'sin-jornada');
            });

        $metaTitle = 'Fixture - Copa del Folklore Argentino 2026';
        $metaDescription = 'Consulta el fixture completo de la Copa del Folklore Argentino 2026.';
        $breadcrumbs = $this->breadcrumbs('Fixture');

        return view('frontend.folklore_tournaments.fixture', compact('tournament', 'matchesByMatchday', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function groups()
    {
        $tournament = $this->loadTournament();
        $groups = $tournament->groups->map(function ($group) {
            return [
                'group' => $group,
                'standings' => $this->standingService->calculateGroup($group),
                'matches' => $group->matches->sortBy([
                    ['matchday', 'asc'],
                    ['id', 'asc'],
                ])->values(),
            ];
        });

        $metaTitle = 'Zonas - Copa del Folklore Argentino 2026';
        $metaDescription = 'Tablas de posiciones, participantes y partidos por zona en la Copa del Folklore Argentino 2026.';
        $breadcrumbs = $this->breadcrumbs('Zonas');

        return view('frontend.folklore_tournaments.groups', compact('tournament', 'groups', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function bracket()
    {
        $tournament = $this->loadTournament();
        $phases = $tournament->matches
            ->where('phase', '!=', FolkloreTournamentMatch::PHASE_GROUP)
            ->groupBy('phase');

        $metaTitle = 'Llaves - Copa del Folklore Argentino 2026';
        $metaDescription = 'Cruces por fase de la Copa del Folklore Argentino 2026.';
        $breadcrumbs = $this->breadcrumbs('Llaves');

        return view('frontend.folklore_tournaments.bracket', compact('tournament', 'phases', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function rules()
    {
        $tournament = $this->loadTournament();
        $metaTitle = 'Reglamento - Copa del Folklore Argentino 2026';
        $metaDescription = 'Reglamento, sistema de puntos y criterios de desempate de la Copa del Folklore Argentino 2026.';
        $breadcrumbs = $this->breadcrumbs('Reglamento');

        return view('frontend.folklore_tournaments.rules', compact('tournament', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    private function loadTournament(): FolkloreTournament
    {
        return FolkloreTournament::query()
            ->where('slug', 'copa-del-folklore-argentino-2026')
            ->with([
                'groups.participants.artist',
                'groups.matches.participant1',
                'groups.matches.participant2',
                'groups.matches.winner',
                'participants.artist',
                'matches.group',
                'matches.participant1',
                'matches.participant2',
                'matches.winner',
            ])
            ->firstOrFail();
    }

    private function breadcrumbs(string $label): array
    {
        return [
            ['label' => 'Copa del Folklore Argentino 2026', 'url' => route('folklore.cup.index')],
            ['label' => $label],
        ];
    }
}
