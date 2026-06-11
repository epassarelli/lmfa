<?php

namespace Tests\Feature;

use App\Models\FolkloreTournament;
use App\Models\FolkloreTournamentGroup;
use App\Models\FolkloreTournamentMatch;
use App\Models\FolkloreTournamentParticipant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FolkloreTournamentFrontendTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_pages_render_without_errors(): void
    {
        $this->seedTournament();

        $this->get(route('folklore.cup.index'))->assertOk()->assertSee('Copa del Folklore Argentino 2026');
        $this->get(route('folklore.cup.participants'))->assertOk()->assertSee('Participantes');
        $this->get(route('folklore.cup.fixture'))->assertOk()->assertSee('Fixture');
        $this->get(route('folklore.cup.groups'))->assertOk()->assertSee('Zonas y tablas');
        $this->get(route('folklore.cup.bracket'))->assertOk()->assertSee('Llaves');
        $this->get(route('folklore.cup.rules'))->assertOk()->assertSee('Reglamento');
    }

    public function test_finished_match_shows_result_and_pending_match_does_not_show_final_result(): void
    {
        [$tournament, $finishedMatch, $pendingMatch] = $this->seedTournament();

        $fixtureResponse = $this->get(route('folklore.cup.fixture'));
        $fixtureResponse->assertOk();
        $fixtureResponse->assertSee("{$finishedMatch->participant_1_votes} - {$finishedMatch->participant_2_votes}");
        $fixtureResponse->assertSee('Pendiente');

        $groupsResponse = $this->get(route('folklore.cup.groups'));
        $groupsResponse->assertOk();
        $groupsResponse->assertSee("{$finishedMatch->participant_1_votes} - {$finishedMatch->participant_2_votes}");
        $groupsResponse->assertSee('Pendiente');
    }

    private function seedTournament(): array
    {
        $tournament = FolkloreTournament::query()->firstOrCreate(
            ['slug' => 'copa-del-folklore-argentino-2026'],
            [
                'name' => 'Copa del Folklore Argentino 2026',
                'year' => 2026,
                'status' => FolkloreTournament::STATUS_ACTIVE,
                'rules' => 'Los resultados se cargan manualmente desde Instagram.',
            ]
        );

        $tournament->matches()->delete();
        $tournament->participants()->delete();
        $tournament->groups()->delete();

        $group = FolkloreTournamentGroup::create([
            'tournament_id' => $tournament->id,
            'name' => 'Zona A',
            'sort_order' => 1,
        ]);

        $participants = collect(['Soledad', 'Los Nocheros', 'Abel Pintos', 'Luciano Pereyra'])->map(function (string $name, int $index) use ($tournament, $group) {
            return FolkloreTournamentParticipant::create([
                'tournament_id' => $tournament->id,
                'group_id' => $group->id,
                'display_name' => $name,
                'seed_order' => $index + 1,
            ]);
        });

        $finishedMatch = FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'phase' => FolkloreTournamentMatch::PHASE_GROUP,
            'matchday' => 1,
            'participant_1_id' => $participants[0]->id,
            'participant_2_id' => $participants[1]->id,
            'participant_1_votes' => 358,
            'participant_2_votes' => 297,
            'winner_participant_id' => $participants[0]->id,
            'status' => FolkloreTournamentMatch::STATUS_FINISHED,
        ]);

        $pendingMatch = FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'phase' => FolkloreTournamentMatch::PHASE_GROUP,
            'matchday' => 2,
            'participant_1_id' => $participants[2]->id,
            'participant_2_id' => $participants[3]->id,
            'status' => FolkloreTournamentMatch::STATUS_SCHEDULED,
        ]);

        FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'phase' => FolkloreTournamentMatch::PHASE_ROUND_16,
            'participant_1_id' => $participants[0]->id,
            'participant_2_id' => $participants[2]->id,
            'status' => FolkloreTournamentMatch::STATUS_SCHEDULED,
        ]);

        return [$tournament, $finishedMatch, $pendingMatch];
    }
}
