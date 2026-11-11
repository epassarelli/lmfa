<?php

namespace Tests\Feature;

use App\Models\Interprete;
use App\Models\News;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeNewsCardsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('CI')) {
            $this->assertSame('mfa_test', DB::connection()->getDatabaseName());
        }
    }

    private function render(News $news): string
    {
        return Blade::render('<x-noticia-card :noticia="$noticia" />', ['noticia' => $news]);
    }

    private function news(array $attrs = []): News
    {
        return new News(array_merge(['title' => 'Noticia sin foto', 'slug' => 'noticia-sin-foto', 'body' => 'Texto'], $attrs));
    }

    public function test_card_sin_imagen_propia_no_renderiza_img(): void
    {
        $html = $this->render($this->news());

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('Noticia sin foto', $html);
    }

    public function test_card_no_usa_foto_del_interprete_relacionado(): void
    {
        $news = $this->news();
        $news->setRelation('interprete', new Interprete(['interprete' => 'Artista', 'slug' => 'artista', 'foto' => 'x.jpg']));

        $this->assertStringNotContainsString('<img', $this->render($news));
    }

    public function test_card_con_imagen_propia_la_conserva(): void
    {
        $html = $this->render($this->news(['featured_image_path' => 'https://example.test/foto.jpg']));

        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('https://example.test/foto.jpg', $html);
    }

    public function test_home_sin_contador_y_con_bajada_fija(): void
    {
        Cache::forget('home:ultimas-noticias');

        $this->get('/')->assertOk()
            ->assertDontSee('Ya reunimos')
            ->assertSee('en un archivo que crece todos los días');
    }
}
