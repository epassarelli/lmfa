<?php

namespace Tests\Feature;

use App\Models\FolkloreTournament;
use App\Models\FolkloreTournamentGroup;
use App\Models\FolkloreTournamentMatch;
use App\Models\FolkloreTournamentParticipant;
use App\Services\FolkloreTournamentFixtureService;
use App\Services\FolkloreTournamentStandingService;
use Carbon\Carbon;
use Database\Seeders\FolkloreTournamentSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FolkloreTournamentBackendTest extends TestCase
{
    use DatabaseTransactions;

    public function test_seeder_creates_tournament_groups_participants_and_group_matches(): void
    {
        $this->seed(FolkloreTournamentSeeder::class);

        $tournament = FolkloreTournament::where('slug', 'copa-del-folklore-argentino-2026')->firstOrFail();

        $this->assertSame(8, $tournament->groups()->count());
        $this->assertSame(32, $tournament->participants()->count());
        $this->assertSame(48, $tournament->matches()->where('phase', FolkloreTournamentMatch::PHASE_GROUP)->count());
        $this->assertSame(4, $tournament->groups()->withCount('participants')->firstWhere('name', 'Zona A')->participants_count);
        $this->assertSame(6, $tournament->groups()->withCount('matches')->firstWhere('name', 'Zona A')->matches_count);
    }

    public function test_fixture_service_generates_six_unique_matches_per_group(): void
    {
        $tournament = FolkloreTournament::create([
            'name' => 'Test Tournament',
            'slug' => 'test-tournament',
            'year' => 2026,
            'status' => FolkloreTournament::STATUS_DRAFT,
        ]);

        $group = FolkloreTournamentGroup::create([
            'tournament_id' => $tournament->id,
            'name' => 'Zona Test',
            'sort_order' => 1,
        ]);

        foreach (range(1, 4) as $index) {
            FolkloreTournamentParticipant::create([
                'tournament_id' => $tournament->id,
                'group_id' => $group->id,
                'display_name' => "Participante {$index}",
                'seed_order' => $index,
            ]);
        }

        $created = app(FolkloreTournamentFixtureService::class)->generateGroupMatches($tournament, $group);

        $this->assertCount(6, $created);
        $this->assertSame([1, 1, 2, 2, 3, 3], $created->pluck('matchday')->sort()->values()->all());

        $pairs = $created->map(function (FolkloreTournamentMatch $match) {
            $pair = [$match->participant_1_id, $match->participant_2_id];
            sort($pair);

            return implode('-', $pair);
        });

        $this->assertCount(6, $pairs->unique());
    }

    public function test_fixture_service_assigns_one_group_match_per_day_starting_tomorrow(): void
    {
        Carbon::setTestNow('2026-06-10 08:00:00');

        try {
            $tournament = FolkloreTournament::create([
                'name' => 'Scheduled Tournament',
                'slug' => 'scheduled-tournament',
                'year' => 2026,
                'status' => FolkloreTournament::STATUS_DRAFT,
            ]);

            foreach ([1 => 'Zona A', 2 => 'Zona B'] as $sortOrder => $name) {
                $group = FolkloreTournamentGroup::create([
                    'tournament_id' => $tournament->id,
                    'name' => $name,
                    'sort_order' => $sortOrder,
                ]);

                foreach (range(1, 4) as $seedOrder) {
                    FolkloreTournamentParticipant::create([
                        'tournament_id' => $tournament->id,
                        'group_id' => $group->id,
                        'display_name' => "{$name} Participante {$seedOrder}",
                        'seed_order' => $seedOrder,
                    ]);
                }
            }

            app(FolkloreTournamentFixtureService::class)->generateGroupStage($tournament);

            $matches = FolkloreTournamentMatch::query()
                ->where('tournament_id', $tournament->id)
                ->orderBy('scheduled_at')
                ->get();

            $this->assertCount(12, $matches);
            $this->assertSame('2026-06-11 12:00:00', $matches->first()->scheduled_at?->format('Y-m-d H:i:s'));
            $this->assertSame('2026-06-22 12:00:00', $matches->last()->scheduled_at?->format('Y-m-d H:i:s'));
            $this->assertCount(12, $matches->pluck('scheduled_at')->map->format('Y-m-d')->unique());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_sync_knockout_stage_creates_round_of_16_when_groups_finish(): void
    {
        Carbon::setTestNow('2026-06-10 08:00:00');

        try {
            [$tournament, $groups] = $this->makeTournamentWithEightGroups();

            foreach ($groups as $groupIndex => $group) {
                $participants = $group->participants()->orderBy('seed_order')->get()->values();
                $this->finishGroupForSeededOrder($tournament, $group, [
                    [$participants[0], $participants[3], 30, 10],
                    [$participants[1], $participants[2], 20, 10],
                    [$participants[0], $participants[2], 30, 10],
                    [$participants[3], $participants[1], 10, 20],
                    [$participants[0], $participants[1], 30, 10],
                    [$participants[2], $participants[3], 20, 10],
                ]);
            }

            app(FolkloreTournamentFixtureService::class)->syncKnockoutStage($tournament);

            $roundOf16 = FolkloreTournamentMatch::query()
                ->where('tournament_id', $tournament->id)
                ->where('phase', FolkloreTournamentMatch::PHASE_ROUND_16)
                ->orderBy('matchday')
                ->get();

            $this->assertCount(8, $roundOf16);
            $this->assertSame('Zona A Participante 1', $roundOf16[0]->participant1->display_name);
            $this->assertSame('Zona B Participante 2', $roundOf16[0]->participant2->display_name);
            $this->assertSame('Zona H Participante 1', $roundOf16[7]->participant1->display_name);
            $this->assertSame('Zona G Participante 2', $roundOf16[7]->participant2->display_name);
            $this->assertSame('2026-07-29 12:00:00', $roundOf16[0]->scheduled_at?->format('Y-m-d H:i:s'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_sync_knockout_stage_advances_winners_until_final_and_third_place(): void
    {
        Carbon::setTestNow('2026-06-10 08:00:00');

        try {
            [$tournament, $groups] = $this->makeTournamentWithEightGroups();

            foreach ($groups as $group) {
                $participants = $group->participants()->orderBy('seed_order')->get()->values();
                $this->finishGroupForSeededOrder($tournament, $group, [
                    [$participants[0], $participants[3], 30, 10],
                    [$participants[1], $participants[2], 20, 10],
                    [$participants[0], $participants[2], 30, 10],
                    [$participants[3], $participants[1], 10, 20],
                    [$participants[0], $participants[1], 30, 10],
                    [$participants[2], $participants[3], 20, 10],
                ]);
            }

            $fixtureService = app(FolkloreTournamentFixtureService::class);
            $fixtureService->syncKnockoutStage($tournament);

            $this->finishPhaseWithTopParticipantWinners($tournament, FolkloreTournamentMatch::PHASE_ROUND_16);
            $fixtureService->syncKnockoutStage($tournament);
            $this->assertSame(4, FolkloreTournamentMatch::query()->where('tournament_id', $tournament->id)->where('phase', FolkloreTournamentMatch::PHASE_QUARTER_FINAL)->count());

            $this->finishPhaseWithTopParticipantWinners($tournament, FolkloreTournamentMatch::PHASE_QUARTER_FINAL);
            $fixtureService->syncKnockoutStage($tournament);
            $this->assertSame(2, FolkloreTournamentMatch::query()->where('tournament_id', $tournament->id)->where('phase', FolkloreTournamentMatch::PHASE_SEMI_FINAL)->count());

            $this->finishPhaseWithTopParticipantWinners($tournament, FolkloreTournamentMatch::PHASE_SEMI_FINAL);
            $fixtureService->syncKnockoutStage($tournament);

            $final = FolkloreTournamentMatch::query()
                ->where('tournament_id', $tournament->id)
                ->where('phase', FolkloreTournamentMatch::PHASE_FINAL)
                ->first();

            $thirdPlace = FolkloreTournamentMatch::query()
                ->where('tournament_id', $tournament->id)
                ->where('phase', FolkloreTournamentMatch::PHASE_THIRD_PLACE)
                ->first();

            $this->assertNotNull($final);
            $this->assertNotNull($thirdPlace);
            $this->assertNotSame($final->participant_1_id, $final->participant_2_id);
            $this->assertNotSame($thirdPlace->participant_1_id, $thirdPlace->participant_2_id);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_standing_service_calculates_points_and_vote_difference(): void
    {
        $tournament = FolkloreTournament::create([
            'name' => 'Standings Tournament',
            'slug' => 'standings-tournament',
            'year' => 2026,
            'status' => FolkloreTournament::STATUS_ACTIVE,
        ]);

        $group = FolkloreTournamentGroup::create([
            'tournament_id' => $tournament->id,
            'name' => 'Zona A',
            'sort_order' => 1,
        ]);

        $participants = collect(range(1, 4))->map(function (int $index) use ($tournament, $group) {
            return FolkloreTournamentParticipant::create([
                'tournament_id' => $tournament->id,
                'group_id' => $group->id,
                'display_name' => "Artista {$index}",
                'seed_order' => $index,
            ]);
        });

        FolkloreTournamentMatch::create([
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

        FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'phase' => FolkloreTournamentMatch::PHASE_GROUP,
            'matchday' => 1,
            'participant_1_id' => $participants[2]->id,
            'participant_2_id' => $participants[3]->id,
            'participant_1_votes' => 100,
            'participant_2_votes' => 100,
            'status' => FolkloreTournamentMatch::STATUS_FINISHED,
        ]);

        FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'phase' => FolkloreTournamentMatch::PHASE_GROUP,
            'matchday' => 2,
            'participant_1_id' => $participants[0]->id,
            'participant_2_id' => $participants[2]->id,
            'participant_1_votes' => 200,
            'participant_2_votes' => 250,
            'winner_participant_id' => $participants[2]->id,
            'status' => FolkloreTournamentMatch::STATUS_FINISHED,
        ]);

        FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'phase' => FolkloreTournamentMatch::PHASE_GROUP,
            'matchday' => 2,
            'participant_1_id' => $participants[1]->id,
            'participant_2_id' => $participants[3]->id,
            'participant_1_votes' => 90,
            'participant_2_votes' => 80,
            'winner_participant_id' => $participants[1]->id,
            'status' => FolkloreTournamentMatch::STATUS_FINISHED,
        ]);

        FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'phase' => FolkloreTournamentMatch::PHASE_GROUP,
            'matchday' => 3,
            'participant_1_id' => $participants[0]->id,
            'participant_2_id' => $participants[3]->id,
            'participant_1_votes' => 111,
            'participant_2_votes' => 110,
            'winner_participant_id' => $participants[0]->id,
            'status' => FolkloreTournamentMatch::STATUS_FINISHED,
        ]);

        FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'phase' => FolkloreTournamentMatch::PHASE_GROUP,
            'matchday' => 3,
            'participant_1_id' => $participants[1]->id,
            'participant_2_id' => $participants[2]->id,
            'participant_1_votes' => 50,
            'participant_2_votes' => 60,
            'winner_participant_id' => $participants[2]->id,
            'status' => FolkloreTournamentMatch::STATUS_FINISHED,
        ]);

        $standings = app(FolkloreTournamentStandingService::class)->calculateGroup($group);

        $this->assertSame($participants[2]->id, $standings->first()['participant_id']);
        $this->assertSame(7, $standings->first()['puntos']);
        $this->assertSame(60, $standings->first()['diferencia']);

        $participantOne = $standings->firstWhere('participant_id', $participants[0]->id);
        $this->assertSame(6, $participantOne['puntos']);
        $this->assertSame(12, $participantOne['diferencia']);

        $participantFour = $standings->firstWhere('participant_id', $participants[3]->id);
        $this->assertSame(1, $participantFour['puntos']);
        $this->assertSame(-11, $participantFour['diferencia']);
    }

    public function test_pending_matches_are_not_counted_in_standings(): void
    {
        $tournament = FolkloreTournament::create([
            'name' => 'Pending Tournament',
            'slug' => 'pending-tournament',
            'year' => 2026,
            'status' => FolkloreTournament::STATUS_ACTIVE,
        ]);

        $group = FolkloreTournamentGroup::create([
            'tournament_id' => $tournament->id,
            'name' => 'Zona B',
            'sort_order' => 2,
        ]);

        $participants = collect(range(1, 4))->map(function (int $index) use ($tournament, $group) {
            return FolkloreTournamentParticipant::create([
                'tournament_id' => $tournament->id,
                'group_id' => $group->id,
                'display_name' => "Participante {$index}",
                'seed_order' => $index,
            ]);
        });

        FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'phase' => FolkloreTournamentMatch::PHASE_GROUP,
            'matchday' => 1,
            'participant_1_id' => $participants[0]->id,
            'participant_2_id' => $participants[1]->id,
            'participant_1_votes' => 500,
            'participant_2_votes' => 400,
            'status' => FolkloreTournamentMatch::STATUS_SCHEDULED,
        ]);

        $standings = app(FolkloreTournamentStandingService::class)->calculateGroup($group);

        $this->assertTrue($standings->every(fn (array $row) => $row['pj'] === 0 && $row['puntos'] === 0));
    }

    private function makeTournamentWithEightGroups(): array
    {
        $tournament = FolkloreTournament::create([
            'name' => 'Knockout Tournament',
            'slug' => 'knockout-tournament',
            'year' => 2026,
            'status' => FolkloreTournament::STATUS_ACTIVE,
        ]);

        $groupNames = ['Zona A', 'Zona B', 'Zona C', 'Zona D', 'Zona E', 'Zona F', 'Zona G', 'Zona H'];
        $groups = collect($groupNames)->map(function (string $name, int $index) use ($tournament) {
            $group = FolkloreTournamentGroup::create([
                'tournament_id' => $tournament->id,
                'name' => $name,
                'sort_order' => $index + 1,
            ]);

            foreach (range(1, 4) as $seedOrder) {
                FolkloreTournamentParticipant::create([
                    'tournament_id' => $tournament->id,
                    'group_id' => $group->id,
                    'display_name' => "{$name} Participante {$seedOrder}",
                    'seed_order' => $seedOrder,
                ]);
            }

            return $group;
        });

        app(FolkloreTournamentFixtureService::class)->generateGroupStage($tournament);

        return [$tournament, $groups];
    }

    private function finishGroupForSeededOrder(FolkloreTournament $tournament, FolkloreTournamentGroup $group, array $results): void
    {
        foreach ($results as $index => [$participant1, $participant2, $votes1, $votes2]) {
            FolkloreTournamentMatch::query()
                ->where('tournament_id', $tournament->id)
                ->where('group_id', $group->id)
                ->where('phase', FolkloreTournamentMatch::PHASE_GROUP)
                ->where('participant_1_id', $participant1->id)
                ->where('participant_2_id', $participant2->id)
                ->update([
                    'participant_1_votes' => $votes1,
                    'participant_2_votes' => $votes2,
                    'winner_participant_id' => $votes1 > $votes2 ? $participant1->id : ($votes2 > $votes1 ? $participant2->id : null),
                    'status' => FolkloreTournamentMatch::STATUS_FINISHED,
                ]);
        }
    }

    private function finishPhaseWithTopParticipantWinners(FolkloreTournament $tournament, string $phase): void
    {
        $matches = FolkloreTournamentMatch::query()
            ->where('tournament_id', $tournament->id)
            ->where('phase', $phase)
            ->orderBy('matchday')
            ->orderBy('id')
            ->get();

        foreach ($matches as $match) {
            $winnerId = min($match->participant_1_id, $match->participant_2_id);
            $winnerVotes = $winnerId === $match->participant_1_id ? 30 : 10;
            $loserVotes = $winnerId === $match->participant_1_id ? 10 : 30;

            $match->update([
                'participant_1_votes' => $winnerId === $match->participant_1_id ? $winnerVotes : $loserVotes,
                'participant_2_votes' => $winnerId === $match->participant_2_id ? $winnerVotes : $loserVotes,
                'winner_participant_id' => $winnerId,
                'status' => FolkloreTournamentMatch::STATUS_FINISHED,
            ]);
        }
    }
}
