<?php

namespace Tests\Feature\Performance;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicMobilePerformanceBudgetTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function home_and_news_index_use_reduced_initial_news_collections(): void
    {
        Cache::flush();

        for ($i = 1; $i <= 14; $i++) {
            DB::table('news')->insert([
                'title' => 'Noticia publicada '.$i,
                'slug' => 'noticia-publicada-'.$i,
                'body' => '<p>Contenido publicado.</p>',
                'editorial_status' => 'published',
                'published_at' => now()->subDays($i),
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);
        }

        $home = $this->call('GET', '/', [], [], [], $this->serverVariables());
        $home->assertOk();
        $this->assertCount(6, $home->viewData('ultimasNoticias'));

        $news = $this->call('GET', '/noticias-del-folklore-argentino', [], [], [], $this->serverVariables());
        $news->assertOk();
        $this->assertSame(12, $news->viewData('ultimas')->count());
        $this->assertCount(4, $news->viewData('ultimasSidebar'));
    }

    /** @test */
    public function discs_songs_and_artists_indexes_use_smaller_first_pages(): void
    {
        Cache::flush();

        for ($i = 1; $i <= 18; $i++) {
            DB::table('interpretes')->insert([
                'id' => 92000 + $i,
                'interprete' => 'Artista '.$i,
                'slug' => 'artista-'.$i,
                'biografia' => '<p>Biografia.</p>',
                'foto' => 'artista-'.$i.'.jpg',
                'visitas' => $i,
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('albunes')->insert([
                'id' => 93000 + $i,
                'album' => 'Disco '.$i,
                'slug' => 'disco-'.$i,
                'anio' => 2000 + $i,
                'foto' => 'disco-'.$i.'.jpg',
                'visitas' => $i,
                'estado' => 1,
                'interprete_id' => 92000 + $i,
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);

            DB::table('canciones')->insert([
                'id' => 94000 + $i,
                'cancion' => 'Cancion '.$i,
                'slug' => 'cancion-'.$i,
                'letra' => '<p>Letra.</p>',
                'visitas' => $i,
                'estado' => 1,
                'interprete_id' => 92000 + $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $discos = $this->call('GET', '/discografias-del-folklore-argentino', [], [], [], $this->serverVariables());
        $discos->assertOk();
        $this->assertSame(12, $discos->viewData('discos')->count());

        $canciones = $this->call('GET', '/letras-de-canciones-folkloricas', [], [], [], $this->serverVariables());
        $canciones->assertOk();
        $this->assertSame(18, $canciones->viewData('canciones')->count());

        $interpretes = $this->call('GET', '/biografias-de-artistas-folkloricos', [], [], [], $this->serverVariables());
        $interpretes->assertOk();
        $this->assertSame(12, $interpretes->viewData('interpretes')->count());
    }

    /** @test */
    public function recipe_and_myth_letter_pages_keep_secondary_blocks_small(): void
    {
        Cache::flush();

        for ($i = 1; $i <= 8; $i++) {
            DB::table('comidas')->insert([
                'id' => 95000 + $i,
                'titulo' => 'Comida c'.$i,
                'slug' => 'comida-c'.$i,
                'receta' => '<p>Receta.</p>',
                'foto' => 'comida-c'.$i.'.jpg',
                'visitas' => $i,
                'estado' => 1,
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);

            DB::table('mitos')->insert([
                'id' => 96000 + $i,
                'titulo' => 'Criatura c'.$i,
                'slug' => 'criatura-c'.$i,
                'mito' => '<p>Mito.</p>',
                'foto' => 'criatura-c'.$i.'.jpg',
                'visitas' => $i,
                'estado' => 1,
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);
        }

        $recipes = $this->call('GET', '/recetas-de-comidas-tipicas-argentinas/letra/c', [], [], [], $this->serverVariables());
        $recipes->assertOk();
        $this->assertCount(6, $recipes->viewData('ultimas'));
        $this->assertCount(6, $recipes->viewData('visitadas'));

        $myths = $this->call('GET', '/mitos-y-leyendas-argentinas/letra/c', [], [], [], $this->serverVariables());
        $myths->assertOk();
        $this->assertCount(6, $myths->viewData('ultimos'));
        $this->assertCount(6, $myths->viewData('visitados'));
    }

    private function serverVariables(): array
    {
        return [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ];
    }
}
