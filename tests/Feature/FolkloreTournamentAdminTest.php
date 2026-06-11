<?php

namespace Tests\Feature;

use Carbon\Carbon;
use App\Models\FolkloreTournament;
use App\Models\FolkloreTournamentGroup;
use App\Models\FolkloreTournamentMatch;
use App\Models\FolkloreTournamentParticipant;
use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FolkloreTournamentAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_tournament_index_requires_authentication(): void
    {
        $this->get('/admin/folklore-tournaments')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_tournaments_index_and_detail(): void
    {
        $user = User::factory()->create();
        $tournament = FolkloreTournament::create([
            'name' => 'Copa del Folklore Argentino 2026 Admin',
            'slug' => 'copa-del-folklore-argentino-2026-admin',
            'year' => 2026,
            'status' => FolkloreTournament::STATUS_ACTIVE,
        ]);

        $group = FolkloreTournamentGroup::create([
            'tournament_id' => $tournament->id,
            'name' => 'Zona A',
            'sort_order' => 1,
        ]);

        FolkloreTournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'display_name' => 'Soledad',
            'seed_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('backend.folklore-tournaments.index'))
            ->assertOk()
            ->assertSee('Copa del Folklore Argentino 2026 Admin');

        $this->actingAs($user)
            ->get(route('backend.folklore-tournaments.show', $tournament))
            ->assertOk()
            ->assertSee('Zona A')
            ->assertSee('Soledad');
    }

    public function test_authenticated_user_can_view_matches_listing(): void
    {
        $user = User::factory()->create();
        [$tournament, $match] = $this->makeTournamentWithMatch();

        $this->actingAs($user)
            ->get(route('backend.folklore-tournaments.matches', $tournament))
            ->assertOk()
            ->assertSee($match->participant1->display_name)
            ->assertSee($match->participant2->display_name);
    }

    public function test_authenticated_user_can_edit_tournament_status_and_participant_zones(): void
    {
        Carbon::setTestNow('2026-06-10 09:00:00');

        try {
            $user = User::factory()->create();
            $tournament = FolkloreTournament::create([
                'name' => 'Copa Test',
                'slug' => 'copa-test-admin',
                'year' => 2026,
                'status' => FolkloreTournament::STATUS_DRAFT,
            ]);

            $groupA = FolkloreTournamentGroup::create([
                'tournament_id' => $tournament->id,
                'name' => 'Zona A',
                'sort_order' => 1,
            ]);

            $groupB = FolkloreTournamentGroup::create([
                'tournament_id' => $tournament->id,
                'name' => 'Zona B',
                'sort_order' => 2,
            ]);

            $participants = collect(range(1, 8))->map(function (int $index) use ($tournament, $groupA, $groupB) {
                return FolkloreTournamentParticipant::create([
                    'tournament_id' => $tournament->id,
                    'group_id' => $index <= 4 ? $groupA->id : $groupB->id,
                    'display_name' => "Participante {$index}",
                    'seed_order' => $index,
                ]);
            });

            $payload = [
                'name' => 'Copa Test Ajustada',
                'slug' => 'copa-test-admin',
                'description' => 'Descripcion editada',
                'year' => 2026,
                'status' => FolkloreTournament::STATUS_ACTIVE,
                'starts_at' => '2026-06-11 12:00:00',
                'ends_at' => '2026-07-28 12:00:00',
                'rules' => 'Reglas actualizadas',
                'participant_artists' => $participants->mapWithKeys(function (FolkloreTournamentParticipant $participant) {
                    $artist = Interprete::create([
                        'interprete' => "Interprete {$participant->seed_order}",
                        'slug' => "interprete-{$participant->seed_order}",
                        'biografia' => 'Biografia de prueba',
                        'estado' => 1,
                    ]);

                    return [$participant->id => $artist->id];
                })->all(),
                'participant_groups' => $participants->mapWithKeys(function (FolkloreTournamentParticipant $participant) use ($groupA, $groupB) {
                    $targetGroupId = $participant->seed_order === 1 ? $groupB->id : ($participant->seed_order === 5 ? $groupA->id : $participant->group_id);

                    return [$participant->id => $targetGroupId];
                })->all(),
            ];

            $response = $this->actingAs($user)
                ->put(route('backend.folklore-tournaments.update', $tournament), $payload);

            $response->assertRedirect(route('backend.folklore-tournaments.show', $tournament));

            $this->assertDatabaseHas('folklore_tournaments', [
                'id' => $tournament->id,
                'name' => 'Copa Test Ajustada',
                'status' => FolkloreTournament::STATUS_ACTIVE,
            ]);

            $this->assertDatabaseHas('folklore_tournament_participants', [
                'id' => $participants[0]->id,
                'group_id' => $groupB->id,
                'artist_id' => $payload['participant_artists'][$participants[0]->id],
                'display_name' => 'Interprete 1',
            ]);

            $this->assertDatabaseHas('folklore_tournament_participants', [
                'id' => $participants[4]->id,
                'group_id' => $groupA->id,
                'artist_id' => $payload['participant_artists'][$participants[4]->id],
                'display_name' => 'Interprete 5',
            ]);

            $scheduledMatches = FolkloreTournamentMatch::query()
                ->where('tournament_id', $tournament->id)
                ->where('phase', FolkloreTournamentMatch::PHASE_GROUP)
                ->orderBy('scheduled_at')
                ->get();

            $this->assertCount(12, $scheduledMatches);
            $this->assertSame('2026-06-11 12:00:00', $scheduledMatches->first()->scheduled_at?->format('Y-m-d H:i:s'));
            $this->assertCount(12, $scheduledMatches->pluck('scheduled_at')->map->format('Y-m-d')->unique());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_authenticated_user_can_update_match_votes_status_winner_and_instagram_url(): void
    {
        $user = User::factory()->create();
        [, $match] = $this->makeTournamentWithMatch();

        $response = $this->actingAs($user)
            ->put(route('backend.folklore-tournament-matches.update', $match), [
                'participant_1_id' => $match->participant_1_id,
                'participant_2_id' => $match->participant_2_id,
                'participant_1_votes' => 358,
                'participant_2_votes' => 297,
                'winner_participant_id' => $match->participant_1_id,
                'status' => FolkloreTournamentMatch::STATUS_FINISHED,
                'instagram_url' => 'https://instagram.com/p/abc123',
                'notes' => 'Conteo cerrado manualmente.',
            ]);

        $response->assertRedirect(route('backend.folklore-tournaments.matches', $match->tournament_id));

        $this->assertDatabaseHas('folklore_tournament_matches', [
            'id' => $match->id,
            'participant_1_votes' => 358,
            'participant_2_votes' => 297,
            'winner_participant_id' => $match->participant_1_id,
            'status' => FolkloreTournamentMatch::STATUS_FINISHED,
            'instagram_url' => 'https://instagram.com/p/abc123',
        ]);
    }

    public function test_match_update_validates_manual_winner_against_match_participants(): void
    {
        $user = User::factory()->create();
        [, $match] = $this->makeTournamentWithMatch();

        $outsider = FolkloreTournamentParticipant::create([
            'tournament_id' => $match->tournament_id,
            'display_name' => 'Outsider',
            'seed_order' => 99,
        ]);

        $this->actingAs($user)
            ->from(route('backend.folklore-tournament-matches.edit', $match))
            ->put(route('backend.folklore-tournament-matches.update', $match), [
                'participant_1_id' => $match->participant_1_id,
                'participant_2_id' => $match->participant_2_id,
                'participant_1_votes' => 10,
                'participant_2_votes' => 10,
                'winner_participant_id' => $outsider->id,
                'status' => FolkloreTournamentMatch::STATUS_FINISHED,
            ])
            ->assertRedirect(route('backend.folklore-tournament-matches.edit', $match))
            ->assertSessionHasErrors('winner_participant_id');
    }

    public function test_authenticated_user_can_adjust_knockout_match_participants_manually(): void
    {
        $user = User::factory()->create();
        $tournament = FolkloreTournament::create([
            'name' => 'Copa Finales',
            'slug' => 'copa-finales',
            'year' => 2026,
            'status' => FolkloreTournament::STATUS_ACTIVE,
        ]);

        $participant1 = FolkloreTournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'display_name' => 'Artista 1',
            'seed_order' => 1,
        ]);

        $participant2 = FolkloreTournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'display_name' => 'Artista 2',
            'seed_order' => 2,
        ]);

        $participant3 = FolkloreTournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'display_name' => 'Artista 3',
            'seed_order' => 3,
        ]);

        $match = FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'phase' => FolkloreTournamentMatch::PHASE_ROUND_16,
            'matchday' => 1,
            'participant_1_id' => $participant1->id,
            'participant_2_id' => $participant2->id,
            'status' => FolkloreTournamentMatch::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($user)
            ->put(route('backend.folklore-tournament-matches.update', $match), [
                'participant_1_id' => $participant1->id,
                'participant_2_id' => $participant3->id,
                'participant_1_votes' => 200,
                'participant_2_votes' => 150,
                'status' => FolkloreTournamentMatch::STATUS_FINISHED,
                'winner_participant_id' => $participant1->id,
            ]);

        $response->assertRedirect(route('backend.folklore-tournaments.matches', $tournament));

        $this->assertDatabaseHas('folklore_tournament_matches', [
            'id' => $match->id,
            'participant_1_id' => $participant1->id,
            'participant_2_id' => $participant3->id,
            'winner_participant_id' => $participant1->id,
            'status' => FolkloreTournamentMatch::STATUS_FINISHED,
        ]);
    }

    private function makeTournamentWithMatch(): array
    {
        $tournament = FolkloreTournament::create([
            'name' => 'Copa Test',
            'slug' => 'copa-test',
            'year' => 2026,
            'status' => FolkloreTournament::STATUS_ACTIVE,
        ]);

        $group = FolkloreTournamentGroup::create([
            'tournament_id' => $tournament->id,
            'name' => 'Zona B',
            'sort_order' => 2,
        ]);

        $participant1 = FolkloreTournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'display_name' => 'Los Nocheros',
            'seed_order' => 1,
        ]);

        $participant2 = FolkloreTournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'display_name' => 'Abel Pintos',
            'seed_order' => 2,
        ]);

        $match = FolkloreTournamentMatch::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'phase' => FolkloreTournamentMatch::PHASE_GROUP,
            'matchday' => 1,
            'participant_1_id' => $participant1->id,
            'participant_2_id' => $participant2->id,
            'status' => FolkloreTournamentMatch::STATUS_SCHEDULED,
        ]);

        return [$tournament, $match->fresh(['participant1', 'participant2'])];
    }
}
