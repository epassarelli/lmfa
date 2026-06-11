<?php

namespace Database\Seeders;

use App\Models\FolkloreTournament;
use App\Models\FolkloreTournamentGroup;
use App\Models\FolkloreTournamentParticipant;
use App\Models\Interprete;
use App\Services\FolkloreTournamentFixtureService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FolkloreTournamentSeeder extends Seeder
{
    private const PARTICIPANTS = [
        'Soledad',
        'Los Nocheros',
        'Chaqueño Palavecino',
        'Abel Pintos',
        'Luciano Pereyra',
        'Mercedes Sosa',
        'Atahualpa Yupanqui',
        'Horacio Guarany',
        'Jorge Rojas',
        'Sergio Galleguillo',
        'Peteco Carabajal',
        'Raly Barrionuevo',
        'Los Tekis',
        'Los Manseros Santiagueños',
        'Dúo Coplanacu',
        'Los Chalchaleros',
        'Los Fronterizos',
        'Teresa Parodi',
        'Tamara Castro',
        'El Indio Lucio Rojas',
        'Destino San Javier',
        'Ahyre',
        'Campedrinos',
        'Nahuel Pennisi',
        'Los Carabajal',
        'Cuti y Roberto Carabajal',
        'Suna Rocha',
        'Bruno Arias',
        'Mariana Carrizo',
        'La Bruja Salguero',
        'Orellana Lucca',
        'Facundo Toro',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $tournament = FolkloreTournament::updateOrCreate(
                ['slug' => 'copa-del-folklore-argentino-2026'],
                [
                    'name' => 'Copa del Folklore Argentino 2026',
                    'description' => 'Torneo editorial y participativo entre interpretes del folklore argentino.',
                    'year' => 2026,
                    'status' => FolkloreTournament::STATUS_DRAFT,
                    'rules' => 'Los resultados se cargan manualmente desde publicaciones oficiales de Instagram.',
                ]
            );

            $groups = collect(range('A', 'H'))->map(function (string $letter, int $index) use ($tournament) {
                return FolkloreTournamentGroup::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'name' => "Zona {$letter}",
                    ],
                    [
                        'slug' => "zona-{$letter}",
                        'sort_order' => $index + 1,
                    ]
                );
            });

            foreach (array_chunk(self::PARTICIPANTS, 4) as $groupIndex => $participantNames) {
                $group = $groups[$groupIndex];

                foreach ($participantNames as $seedOffset => $participantName) {
                    $artist = Interprete::query()
                        ->whereRaw('LOWER(interprete) = ?', [mb_strtolower($participantName)])
                        ->first();

                    FolkloreTournamentParticipant::updateOrCreate(
                        [
                            'tournament_id' => $tournament->id,
                            'display_name' => $participantName,
                        ],
                        [
                            'group_id' => $group->id,
                            'artist_id' => $artist?->id,
                            'seed_order' => ($groupIndex * 4) + $seedOffset + 1,
                            'status' => FolkloreTournamentParticipant::STATUS_ACTIVE,
                            'image_path' => null,
                        ]
                    );
                }
            }

            app(FolkloreTournamentFixtureService::class)->rebuildGroupStage($tournament->fresh());
        });
    }
}
