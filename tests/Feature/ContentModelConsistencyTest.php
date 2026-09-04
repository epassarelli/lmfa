<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NewsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentModelConsistencyTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function artist_news_pages_use_news_table_and_do_not_duplicate_results(): void
    {
        $categoriaId = $this->createCategoria('general-artista-consistencia');
        $interpreteId = $this->createInterprete('Artista Consistencia', 'artista-consistencia');

        $newsId = DB::table('news')->insertGetId([
            'title' => 'Noticia vigente artista',
            'slug' => 'noticia-vigente-artista',
            'body' => 'Contenido publicado del artista',
            'categoria_id' => $categoriaId,
            'editorial_status' => 'published',
            'estado' => 1,
            'interprete_id' => $interpreteId,
            'published_at' => now()->subHour(),
            'created_at' => now()->subHour(),
            'updated_at' => now(),
        ]);

        DB::table('interprete_noticia')->insert([
            'interprete_id' => $interpreteId,
            'noticia_id' => $newsId,
        ]);

        $response = $this->call('GET', '/artista-consistencia/noticias', [], [], [], $this->serverVariables());
        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), '<article class="bg-white shadow-md'));

        $artistHome = $this->call('GET', '/artista-consistencia', [], [], [], $this->serverVariables());
        $artistHome->assertOk();
        $this->assertSame(1, substr_count($artistHome->getContent(), '<article class="bg-white shadow-md'));
    }

    /** @test */
    public function future_or_draft_news_do_not_appear_as_published_for_the_artist(): void
    {
        $categoriaId = $this->createCategoria('general-artista-futuro');
        $interpreteId = $this->createInterprete('Artista Futuro', 'artista-futuro');

        DB::table('news')->insert([
            [
                'title' => 'Noticia futura artista',
                'slug' => 'noticia-futura-artista',
                'body' => 'Contenido futuro',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'published',
                'estado' => 1,
                'interprete_id' => $interpreteId,
                'published_at' => now()->addDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Noticia draft artista',
                'slug' => 'noticia-draft-artista',
                'body' => 'Contenido draft',
                'categoria_id' => $categoriaId,
                'editorial_status' => 'draft',
                'estado' => 0,
                'interprete_id' => $interpreteId,
                'published_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->call('GET', '/artista-futuro/noticias', [], [], [], $this->serverVariables());
        $response->assertOk();
        $response->assertDontSee('Noticia futura artista');
        $response->assertDontSee('Noticia draft artista');
    }

    /** @test */
    public function artist_shows_and_public_schedule_read_from_events(): void
    {
        $author = User::factory()->create();
        $interpreteId = $this->createInterprete('Artista Eventos', 'artista-eventos');

        $eventId = DB::table('events')->insertGetId([
            'title' => 'Evento vigente artista',
            'slug' => 'evento-vigente-artista',
            'body' => 'Detalle evento',
            'start_at' => now()->addDays(3),
            'published_at' => now()->subHour(),
            'editorial_status' => 'published',
            'status' => 'active',
            'created_by' => $author->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('event_interprete')->insert([
            'event_id' => $eventId,
            'interprete_id' => $interpreteId,
        ]);

        $artistShows = $this->call('GET', '/artista-eventos/shows', [], [], [], $this->serverVariables());
        $artistShows->assertOk();
        $artistShows->assertSee('Evento vigente artista');

        $schedule = $this->call('GET', '/cartelera-de-eventos-folkloricos', [], [], [], $this->serverVariables());
        $schedule->assertOk();
        $schedule->assertSee('Evento vigente artista');
    }

    /** @test */
    public function new_news_entries_do_not_store_the_primary_artist_in_the_legacy_pivot(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);
        $this->actingAs($admin);

        $categoriaId = $this->createCategoria('general-consistencia-servicio');
        $primaryInterpreteId = $this->createInterprete('Principal Consistencia', 'principal-consistencia');
        $secondaryInterpreteId = $this->createInterprete('Secundario Consistencia', 'secundario-consistencia');

        $news = app(NewsService::class)->createNews([
            'title' => 'Alta nueva sin pivot principal',
            'slug' => 'alta-nueva-sin-pivot-principal',
            'body' => 'Contenido',
            'categoria_id' => $categoriaId,
            'editorial_status' => 'published',
            'interprete_principal_id' => $primaryInterpreteId,
            'interprete_secundarios' => [$primaryInterpreteId, $secondaryInterpreteId],
        ]);

        $this->assertSame($primaryInterpreteId, $news->interprete_id);
        $this->assertDatabaseMissing('interprete_noticia', [
            'interprete_id' => $primaryInterpreteId,
            'noticia_id' => $news->id,
        ]);
        $this->assertDatabaseHas('interprete_noticia', [
            'interprete_id' => $secondaryInterpreteId,
            'noticia_id' => $news->id,
        ]);
    }

    private function createCategoria(string $slug): int
    {
        return DB::table('categorias')->insertGetId([
            'nombre' => 'General',
            'slug' => $slug,
            'metetittle' => 'General',
            'metadescription' => 'Categoria general',
        ]);
    }

    private function createInterprete(string $name, string $slug): int
    {
        $author = User::factory()->create();

        return DB::table('interpretes')->insertGetId([
            'interprete' => $name,
            'slug' => $slug,
            'biografia' => 'Bio',
            'estado' => 1,
            'user_id' => $author->id,
            'visitas' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function serverVariables(): array
    {
        return [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ];
    }
}
