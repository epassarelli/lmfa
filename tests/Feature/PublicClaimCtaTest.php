<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicClaimCtaTest extends TestCase
{
    use DatabaseTransactions;

    private function artist(): Interprete
    {
        return Interprete::create(['interprete' => 'Artista '.uniqid(), 'slug' => 'artista-'.uniqid(), 'biografia' => '<p>Biografía de prueba.</p>', 'estado' => 1, 'user_id' => $this->baseUserId()]);
    }

    private function festival(): Festival
    {
        $mes = Mes::first() ?: Mes::create(['nombre' => 'Enero']);
        $province = Provincia::first() ?: Provincia::create(['nombre' => 'Provincia de prueba']);
        $title = 'Festival '.uniqid();

        return Festival::forceCreate([
            'title' => $title, 'slug' => Str::slug($title), 'body' => '<p>Detalle del festival.</p>', 'status' => 'published', 'published_at' => now()->subDay(),
            'mes_id' => $mes->id, 'province_id' => $province->id, 'user_id' => $this->baseUserId(),
        ]);
    }

    public function test_artist_page_offers_to_claim_the_ficha_with_the_artist_preselected(): void
    {
        $artist = $this->artist();
        $link = route('backend.my-entities.index', ['type' => 'interprete', 'id' => $artist->id]);

        $html = $this->get(route('artista.show', $artist->slug))->assertOk()
            ->assertSee('¿Sos este artista o lo representás?')
            ->assertSee('Solicitá administrar su ficha')
            ->assertSee('href="'.e($link).'"', false)
            ->getContent();

        $this->assertStringContainsString('data-nosnippet', $html);
        $this->assertStringContainsString('rel="nofollow"', $html);

        $this->get(route('artista.biografia', $artist->slug))->assertOk()
            ->assertSee('Solicitá administrar su ficha')
            ->assertSee('href="'.e($link).'"', false);
    }

    public function test_festival_page_offers_to_claim_the_ficha(): void
    {
        $festival = $this->festival();
        $link = route('backend.my-entities.index', ['type' => 'festival', 'id' => $festival->id]);

        $this->get('/festivales-y-fiestas-tradicionales/'.$festival->slug)->assertOk()
            ->assertSee('¿Organizás este festival?')
            ->assertSee('href="'.e($link).'"', false);
    }

    public function test_event_and_song_pages_do_not_show_the_claim_link(): void
    {
        $event = Event::create(['title' => 'Evento '.uniqid(), 'slug' => 'evento-'.uniqid(), 'body' => '<p>Detalle</p>', 'start_at' => now()->addWeek(), 'editorial_status' => 'published', 'status' => 'active', 'modality' => 'presencial']);

        $this->get(route('cartelera.show', $event->slug))->assertOk()->assertDontSee('Solicitá administrar su ficha');
    }

    public function test_guest_goes_through_login_and_lands_on_my_fichas_with_the_ficha_selected(): void
    {
        $artist = $this->artist();
        $link = route('backend.my-entities.index', ['type' => 'interprete', 'id' => $artist->id]);

        $this->get($link)->assertRedirect(route('login'));

        $user = User::factory()->create();
        $intended = route('backend.my-entities.index', ['id' => $artist->id, 'type' => 'interprete']); // la URL intended queda con los parámetros ordenados
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect($intended);

        $this->get($link)->assertOk()->assertSee($artist->interprete.' (#'.$artist->id.')');
    }

    public function test_the_public_pages_keep_a_single_h1_and_no_contribution_links(): void
    {
        $artist = $this->artist();
        $html = $this->get(route('artista.show', $artist->slug))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringNotContainsString('contribuir', $html);
        $this->assertStringNotContainsString('Sugerir', $html);
    }
}
