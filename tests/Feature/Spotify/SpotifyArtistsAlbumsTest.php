<?php

namespace Tests\Feature\Spotify;

use App\Models\Album;
use App\Models\Interprete;
use App\Models\User;
use App\Services\Spotify\SpotifyArtistLinker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Spec: project/docs/specs/spotify-artistas-discos.md (todo con Http::fake, sin llamadas reales). */
class SpotifyArtistsAlbumsTest extends TestCase
{
    use DatabaseTransactions;

    private const SP_ARTIST = '1AbCdEfGhIjKlMnOpQrStU'; // 22 caracteres
    private const SP_OTHER = '2ZyXwVuTsRqPoNmLkJiHgF';

    protected function setUp(): void
    {
        parent::setUp();

        config(['spotify.client_id' => 'test-id', 'spotify.client_secret' => 'test-secret']);
        Cache::forget('spotify:client-credentials-token');
        Storage::fake('public');
        Storage::fake('local');
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $user->assignRole('administrador');

        return $user;
    }

    private function artist(string $name, array $extra = []): Interprete
    {
        return Interprete::unguarded(fn () => Interprete::create(array_merge([
            'interprete' => $name,
            'slug' => 'a-'.uniqid(),
            'biografia' => '<p>b</p>',
            'estado' => 1,
            'user_id' => 1,
        ], $extra)));
    }

    private function jpeg(): string
    {
        $img = imagecreatetruecolor(640, 640);
        ob_start();
        imagejpeg($img);

        return (string) ob_get_clean();
    }

    private function spotifyArtist(string $id, string $name): array
    {
        return ['id' => $id, 'name' => $name, 'followers' => ['total' => 1234], 'genres' => ['folklore argentino'], 'images' => [], 'external_urls' => ['spotify' => 'https://open.spotify.com/artist/'.$id]];
    }

    private function spotifyAlbum(string $id, string $name, string $date, string $type = 'album', ?string $artistId = null): array
    {
        return [
            'id' => $id, 'name' => $name, 'release_date' => $date, 'album_type' => $type,
            'artists' => [['id' => $artistId ?? self::SP_ARTIST]],
            'images' => [['url' => 'https://i.scdn.co/image/'.$id, 'width' => 640], ['url' => 'https://i.scdn.co/image/'.$id.'s', 'width' => 300]],
        ];
    }

    private function fakeSpotify(array $searchItems = [], array $albums = []): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response(['artists' => ['items' => $searchItems]]),
            'api.spotify.com/v1/artists/*/albums*' => Http::response(['items' => $albums, 'next' => null]),
            'api.spotify.com/v1/artists/*' => Http::response($this->spotifyArtist(self::SP_ARTIST, 'Cualquiera')),
            'i.scdn.co/*' => Http::response($this->jpeg(), 200, ['Content-Type' => 'image/jpeg']),
        ]);
    }

    // --- Vínculo ---

    public function test_extract_id_accepts_url_uri_and_plain_id_and_rejects_garbage(): void
    {
        $this->assertSame(self::SP_ARTIST, SpotifyArtistLinker::extractId('https://open.spotify.com/artist/'.self::SP_ARTIST.'?si=abc'));
        $this->assertSame(self::SP_ARTIST, SpotifyArtistLinker::extractId('spotify:artist:'.self::SP_ARTIST));
        $this->assertSame(self::SP_ARTIST, SpotifyArtistLinker::extractId(self::SP_ARTIST));
        $this->assertNull(SpotifyArtistLinker::extractId('https://example.com/artist/x'));
        $this->assertNull(SpotifyArtistLinker::extractId(''));
    }

    public function test_auto_link_links_a_single_exact_match_ignoring_accents_and_case(): void
    {
        $this->fakeSpotify([$this->spotifyArtist(self::SP_ARTIST, 'JULIA ELENA DAVALOS'), $this->spotifyArtist(self::SP_OTHER, 'Julia Elena Dávalos Tributo')]);
        $artist = $this->artist('Julia Elena Dávalos');

        $this->artisan('spotify:vincular-artistas', ['--limit' => 500])->assertSuccessful();

        $this->assertSame(self::SP_ARTIST, $artist->fresh()->spotify_artist_id);
        $this->assertNotNull($artist->fresh()->spotify_link_checked_at);
    }

    public function test_auto_link_does_not_link_ambiguous_or_missing_matches(): void
    {
        $this->fakeSpotify([$this->spotifyArtist(self::SP_ARTIST, 'Los Tucu'), $this->spotifyArtist(self::SP_OTHER, 'Los Tucu')]);
        $ambiguous = $this->artist('Los Tucu');
        $missing = $this->artist('Nadie Coincide '.uniqid());

        $this->artisan('spotify:vincular-artistas', ['--limit' => 500])->assertSuccessful();

        $this->assertNull($ambiguous->fresh()->spotify_artist_id);
        $this->assertNull($missing->fresh()->spotify_artist_id);
        $this->assertNotNull($missing->fresh()->spotify_link_checked_at);
    }

    public function test_auto_link_never_overwrites_an_existing_link_nor_reuses_an_id(): void
    {
        $this->fakeSpotify([$this->spotifyArtist(self::SP_ARTIST, 'Cafrune')]);
        $already = $this->artist('Cafrune', ['spotify_artist_id' => self::SP_OTHER]);
        $this->artist('Otro Cafrune', ['spotify_artist_id' => self::SP_ARTIST]);
        $twin = $this->artist('Cafrune');

        $this->artisan('spotify:vincular-artistas', ['--limit' => 500])->assertSuccessful();

        $this->assertSame(self::SP_OTHER, $already->fresh()->spotify_artist_id);
        $this->assertNull($twin->fresh()->spotify_artist_id, 'el ID ya está usado por otro artista');
    }

    public function test_dry_run_does_not_write(): void
    {
        $this->fakeSpotify([$this->spotifyArtist(self::SP_ARTIST, 'Atahualpa Test')]);
        $artist = $this->artist('Atahualpa Test');

        $this->artisan('spotify:vincular-artistas', ['--limit' => 500, '--dry-run' => true])->assertSuccessful();

        $this->assertNull($artist->fresh()->spotify_artist_id);
        $this->assertNull($artist->fresh()->spotify_link_checked_at);
    }

    public function test_manual_link_validates_format_and_uniqueness(): void
    {
        $this->fakeSpotify();
        $admin = $this->admin();
        $artist = $this->artist('Manual QA');
        $this->artist('Dueño QA', ['spotify_artist_id' => self::SP_OTHER]);

        $this->actingAs($admin)->post(route('backend.interpretes.spotify.link', $artist), ['spotify' => 'esto no es un enlace'])
            ->assertSessionHas('error');
        $this->actingAs($admin)->post(route('backend.interpretes.spotify.link', $artist), ['spotify' => 'spotify:artist:'.self::SP_OTHER])
            ->assertSessionHas('error');
        $this->assertNull($artist->fresh()->spotify_artist_id);

        $this->actingAs($admin)->post(route('backend.interpretes.spotify.link', $artist), ['spotify' => 'https://open.spotify.com/artist/'.self::SP_ARTIST])
            ->assertSessionHas('success');
        $this->assertSame(self::SP_ARTIST, $artist->fresh()->spotify_artist_id);

        $this->actingAs($admin)->delete(route('backend.interpretes.spotify.unlink', $artist))->assertRedirect();
        $this->assertNull($artist->fresh()->spotify_artist_id);
    }

    public function test_user_without_permission_cannot_link_or_import(): void
    {
        $this->fakeSpotify();
        $artist = $this->artist('Sin Permiso QA', ['spotify_artist_id' => self::SP_ARTIST]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('backend.interpretes.spotify.link', $artist), ['spotify' => self::SP_OTHER])->assertForbidden();
        $this->actingAs($user)->post(route('backend.interpretes.spotify.import', $artist), ['albums' => ['A234567890123456789012']])->assertForbidden();
        $this->actingAs($user)->post(route('backend.interpretes.spotify.bulk-link'))->assertForbidden();
    }

    public function test_bulk_link_from_backoffice_links_only_exact_unique_matches(): void
    {
        $this->fakeSpotify([$this->spotifyArtist(self::SP_ARTIST, 'Masivo QA')]);
        $artist = $this->artist('Masivo QA');

        $this->actingAs($this->admin())->post(route('backend.interpretes.spotify.bulk-link'))->assertSessionHas('success');

        $this->assertSame(self::SP_ARTIST, $artist->fresh()->spotify_artist_id);
    }

    // --- Pantalla y listado ---

    public function test_spotify_screen_shows_candidates_when_unlinked(): void
    {
        $admin = $this->admin();

        $this->fakeSpotify([$this->spotifyArtist(self::SP_ARTIST, 'Pantalla QA')]);
        $unlinked = $this->artist('Pantalla QA');
        $this->actingAs($admin)->get(route('backend.interpretes.spotify', $unlinked))
            ->assertOk()->assertSee('Elegí el artista correcto')->assertSee('Nombre exacto')->assertSee('Vincular');
    }

    public function test_spotify_screen_lists_new_albums_when_linked(): void
    {
        $admin = $this->admin();
        $this->fakeSpotify([], [$this->spotifyAlbum('B234567890123456789012', 'Disco Nuevo QA', '2020-05-01')]);
        $linked = $this->artist('Pantalla Vinculada QA', ['spotify_artist_id' => self::SP_ARTIST]);
        $this->actingAs($admin)->get(route('backend.interpretes.spotify', $linked))
            ->assertOk()->assertSee('Disco Nuevo QA')->assertSee('Crear discos seleccionados');
    }

    public function test_index_filters_by_spotify_link_and_edit_form_shows_link_state(): void
    {
        $admin = $this->admin();
        $this->artist('Filtro Vinculado QA', ['spotify_artist_id' => self::SP_ARTIST]);
        $this->artist('Filtro Pendiente QA');

        $this->actingAs($admin)->get(route('backend.interpretes.index', ['spotify' => 'sin_vincular', 'search' => 'Filtro']))
            ->assertOk()->assertSee('Filtro Pendiente QA')->assertDontSee('Filtro Vinculado QA');
        $this->actingAs($admin)->get(route('backend.interpretes.index', ['spotify' => 'vinculados', 'search' => 'Filtro']))
            ->assertOk()->assertSee('Filtro Vinculado QA')->assertDontSee('Filtro Pendiente QA');

        $linked = Interprete::where('interprete', 'Filtro Vinculado QA')->first();
        $this->actingAs($admin)->get(route('backend.interpretes.edit', $linked))->assertOk()->assertSee('Vinculado a Spotify');
    }

    // --- Importación ---

    public function test_import_creates_inactive_albums_with_embed_and_cover_and_skips_existing(): void
    {
        $admin = $this->admin();
        $artist = $this->artist('Importa QA', ['spotify_artist_id' => self::SP_ARTIST]);
        // Ya existe en el portal con el embed de Spotify: no debe proponerse de nuevo.
        Album::create(['album' => 'Ya Estaba', 'slug' => 'ya-estaba', 'anio' => '2015', 'interprete_id' => $artist->id, 'user_id' => $admin->id, 'estado' => 1, 'visitas' => 0,
            'spotify' => '<iframe src="https://open.spotify.com/embed/album/C234567890123456789012?utm_source=generator"></iframe>']);

        $this->fakeSpotify([], [
            $this->spotifyAlbum('B234567890123456789012', 'Disco Nuevo QA', '2020-05-01'),
            $this->spotifyAlbum('C234567890123456789012', 'Ya Estaba', '2015-01-01'),
            $this->spotifyAlbum('D234567890123456789012', 'Colaboración Ajena', '2019-01-01', 'album', self::SP_OTHER),
            $this->spotifyAlbum('E234567890123456789012', 'Single QA', '2021', 'single'),
            $this->spotifyAlbum('F234567890123456789012', 'Aparece En', '2018-01-01', 'appears_on'),
        ]);

        $this->actingAs($admin)->post(route('backend.interpretes.spotify.import', $artist), ['albums' => ['B234567890123456789012', 'E234567890123456789012', 'C234567890123456789012']])
            ->assertSessionHas('success');

        $new = Album::where('interprete_id', $artist->id)->where('album', 'Disco Nuevo QA')->firstOrFail();
        $this->assertFalse((bool) $new->estado, 'siempre inactivo, aun siendo administrador');
        $this->assertSame('studio', $new->album_type);
        $this->assertSame('2020', (string) $new->anio);
        $this->assertSame('2020-05-01', $new->release_date->format('Y-m-d'));
        $this->assertStringContainsString('open.spotify.com/embed/album/B234567890123456789012', $new->spotify);
        $this->assertSame(1, $new->images()->count(), 'la portada queda guardada');
        $this->assertSame('external', $new->images()->first()->source_type ?? 'external');

        $single = Album::where('interprete_id', $artist->id)->where('album', 'Single QA')->firstOrFail();
        $this->assertSame('single', $single->album_type);
        $this->assertNull($single->release_date, 'fecha parcial: no se inventa el día');

        $this->assertSame(1, Album::where('interprete_id', $artist->id)->where('album', 'Ya Estaba')->count(), 'sin duplicar');
        $this->assertSame(0, Album::where('album', 'Colaboración Ajena')->count());
        $this->assertSame(0, Album::where('album', 'Aparece En')->count());
        $this->assertNotNull($artist->fresh()->spotify_albums_checked_at);
    }

    public function test_import_still_creates_the_album_when_the_cover_fails(): void
    {
        $artist = $this->artist('Sin Portada QA', ['spotify_artist_id' => self::SP_ARTIST]);
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            'api.spotify.com/v1/artists/*/albums*' => Http::response(['items' => [$this->spotifyAlbum('B234567890123456789012', 'Disco Sin Tapa QA', '2020-05-01')], 'next' => null]),
            'i.scdn.co/*' => Http::response('no es imagen', 404),
        ]);

        $this->actingAs($this->admin())->post(route('backend.interpretes.spotify.import', $artist), ['albums' => ['B234567890123456789012']])
            ->assertSessionHas('import_errors');

        $album = Album::where('interprete_id', $artist->id)->where('album', 'Disco Sin Tapa QA')->firstOrFail();
        $this->assertFalse((bool) $album->estado);
        $this->assertSame(0, $album->images()->count());
    }

    public function test_same_title_other_year_is_flagged_as_possible_duplicate_and_unchecked(): void
    {
        $admin = $this->admin();
        $artist = $this->artist('Duplicado QA', ['spotify_artist_id' => self::SP_ARTIST]);
        Album::create(['album' => 'Clásicos', 'slug' => 'clasicos', 'anio' => '2001', 'interprete_id' => $artist->id, 'user_id' => $admin->id, 'estado' => 1, 'visitas' => 0]);
        $this->fakeSpotify([], [$this->spotifyAlbum('B234567890123456789012', 'Clásicos', '2010-01-01')]);

        $html = $this->actingAs($admin)->get(route('backend.interpretes.spotify', $artist))->assertOk()->getContent();

        $this->assertStringContainsString('Posible duplicado', $html);
        $this->assertDoesNotMatchRegularExpression('/value="B234567890123456789012"[^>]*checked/', $html);
    }

    // --- Comando programado ---

    public function test_propose_command_processes_one_artist_respects_max_and_leaves_inactive(): void
    {
        $user = $this->admin();
        $artist = $this->artist('Programada QA', ['spotify_artist_id' => self::SP_ARTIST]);
        $this->fakeSpotify([], [
            $this->spotifyAlbum('B234567890123456789012', 'Uno QA', '2020-01-01'),
            $this->spotifyAlbum('C234567890123456789012', 'Dos QA', '2021-01-01'),
            $this->spotifyAlbum('D234567890123456789012', 'Tres QA', '2022-01-01'),
        ]);

        $this->artisan('spotify:proponer-discos', ['--artist' => $artist->id, '--max' => 2, '--user' => $user->id])->assertSuccessful();

        $created = Album::where('interprete_id', $artist->id)->get();
        $this->assertCount(2, $created);
        $this->assertTrue($created->every(fn ($a) => ! $a->estado && (int) $a->user_id === $user->id));
        $this->assertNull($artist->fresh()->spotify_albums_checked_at, 'quedan pendientes: sigue primero en la rotación');

        $this->artisan('spotify:proponer-discos', ['--artist' => $artist->id, '--max' => 2, '--user' => $user->id])->assertSuccessful();

        $this->assertCount(3, Album::where('interprete_id', $artist->id)->get());
        $this->assertNotNull($artist->fresh()->spotify_albums_checked_at);
    }

    public function test_propose_command_dry_run_and_missing_user_do_not_create_anything(): void
    {
        $artist = $this->artist('Simula QA', ['spotify_artist_id' => self::SP_ARTIST]);
        $this->fakeSpotify([], [$this->spotifyAlbum('B234567890123456789012', 'Uno QA', '2020-01-01')]);

        $this->artisan('spotify:proponer-discos', ['--artist' => $artist->id, '--dry-run' => true])->assertSuccessful();
        $this->artisan('spotify:proponer-discos', ['--artist' => $artist->id, '--user' => 99999999])->assertFailed();

        $this->assertSame(0, Album::where('interprete_id', $artist->id)->count());
    }

    public function test_spotify_failure_is_reported_without_breaking(): void
    {
        $artist = $this->artist('Falla QA', ['spotify_artist_id' => self::SP_ARTIST]);
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            'api.spotify.com/*' => Http::response(['error' => 'forbidden'], 403),
        ]);

        $this->actingAs($this->admin())->get(route('backend.interpretes.spotify', $artist))
            ->assertOk()->assertSee('Premium');
        $this->artisan('spotify:proponer-discos', ['--artist' => $artist->id, '--user' => User::factory()->create()->id])->assertFailed();
    }

    public function test_requests_use_client_credentials_and_never_hit_spotify_without_config(): void
    {
        config(['spotify.client_id' => null, 'spotify.client_secret' => null]);
        Http::fake();

        $this->artisan('spotify:vincular-artistas')->assertFailed();
        Http::assertNothingSent();

    }

    public function test_token_request_uses_client_credentials(): void
    {
        config(['spotify.client_id' => 'test-id', 'spotify.client_secret' => 'test-secret']);
        $this->fakeSpotify([$this->spotifyArtist(self::SP_ARTIST, 'Token QA')]);
        $this->artist('Token QA');
        $this->artisan('spotify:vincular-artistas', ['--limit' => 500])->assertSuccessful();
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'accounts.spotify.com') && ($r['grant_type'] ?? null) === 'client_credentials');
    }
}
