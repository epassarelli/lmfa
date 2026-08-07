<?php

namespace Tests\Feature\Recipes;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecipeAuditCommandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function audit_command_exports_json_inventory_without_modifying_recipes(): void
    {
        Storage::fake('local');

        DB::table('comidas')->insert([
            'id' => 81001,
            'titulo' => 'Paella mixta',
            'slug' => 'paella-mixta',
            'receta' => '<p>Arroz y mariscos.</p>',
            'foto' => 'paella-mixta.jpg',
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now()->subYear(),
            'updated_at' => now()->subDay(),
        ]);

        $exitCode = Artisan::call('recipes:audit', [
            '--format' => 'json',
            '--path' => 'exports/tests',
        ]);

        $this->assertSame(0, $exitCode);

        $files = Storage::disk('local')->files('exports/tests');
        $this->assertCount(1, $files);

        $payload = json_decode(Storage::disk('local')->get($files[0]), true, 512, JSON_THROW_ON_ERROR);
        $entry = collect($payload)->firstWhere('slug', 'paella-mixta');

        $this->assertNotNull($entry);
        $this->assertSame('Paella mixta', $entry['titulo']);
        $this->assertSame('REVISAR_RELEVANCIA', $entry['accion_editorial_sugerida']);
        $this->assertTrue($entry['posible_falta_relacion_cocina_argentina']);
        $this->assertSame('PUBLICADO', $entry['estado']);
        $this->assertSame('https://mifolkloreargentino.com/recetas-de-comidas-tipicas-argentinas/paella-mixta', $entry['url_publica']);

        $this->assertSame(1, DB::table('comidas')->where('slug', 'paella-mixta')->count());
    }

    /** @test */
    public function audit_command_supports_dry_run_without_writing_files(): void
    {
        Storage::fake('local');

        DB::table('comidas')->insert([
            'titulo' => 'Humita en chala',
            'slug' => 'humita-en-chala',
            'receta' => '<p>Maiz, cebolla y coccion suave.</p>',
            'foto' => 'humita-en-chala.jpg',
            'visitas' => 0,
            'estado' => 1,
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subDay(),
        ]);

        $exitCode = Artisan::call('recipes:audit', [
            '--format' => 'both',
            '--path' => 'exports/tests-dry-run',
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);
        Storage::disk('local')->assertDirectoryEmpty('exports/tests-dry-run');
        $this->assertStringContainsString('Dry run activo: no se escribieron archivos.', Artisan::output());
    }
}
