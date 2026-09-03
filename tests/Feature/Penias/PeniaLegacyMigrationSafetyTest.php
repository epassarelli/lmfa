<?php

namespace Tests\Feature\Penias;

use App\Models\PeniaProfile;
use App\Models\Provincia;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PeniaLegacyMigrationSafetyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_retiring_the_legacy_module_preserves_its_rows_and_traceability_bridge(): void
    {
        $legacyId = DB::table('penias')->insertGetId([
            'titulo' => 'Peña legacy preservada',
            'slug' => 'penia-legacy-preservada',
            'detalle' => 'Registro de control para una migración no destructiva.',
            'foto' => 'penia-legacy.jpg',
            'user_id' => null,
            'visitas' => 12,
            'publicar' => null,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $province = Provincia::create(['nombre' => 'Provincia migración '.uniqid()]);
        $profile = PeniaProfile::factory()->create(['province_id' => $province->id]);
        DB::table('penia_profiles')->where('id', $profile->id)->update(['legacy_penia_id' => $legacyId]);

        $migration = require database_path('migrations/2026_09_03_010000_retire_legacy_penias_table.php');
        $migration->up();
        $migration->down();

        $this->assertTrue(Schema::hasTable('penias'));
        $this->assertTrue(Schema::hasColumn('penia_profiles', 'legacy_penia_id'));
        $this->assertDatabaseHas('penias', ['id' => $legacyId, 'slug' => 'penia-legacy-preservada']);
        $this->assertDatabaseHas('penia_profiles', ['id' => $profile->id, 'legacy_penia_id' => $legacyId]);
    }
}
