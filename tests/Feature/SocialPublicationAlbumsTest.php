<?php

namespace Tests\Feature;

use App\Models\{Album, Interprete, PublicationTarget, User};
use App\Services\Publication\{AutomaticAlbumPublication, MetaCredentialStore, SocialPublicationExecutor, SocialPublicationLedgerService, SocialPublicationRenderer, SocialPublicationSettings};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Http, Mail, Queue, Storage};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Spec social-autopublicacion-discos: Discos en la autopublicación, Meta simulado con Http::fake(), sin publicaciones reales. */
class SocialPublicationAlbumsTest extends TestCase
{
    use DatabaseTransactions;

    private User $operator;
    private Interprete $artist;
    private Album $album;
    private string $dir;
    private const PAGE = '145931542128207';
    private const IG = '17841400312117345';
    private const TOKEN = 'EAAtest0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJ';

    protected function setUp(): void
    {
        parent::setUp();
        if (!getenv('CI')) { $this->assertSame('mfa_test', DB::connection()->getDatabaseName()); }
        Queue::fake(); Mail::fake(); Storage::fake('local'); Storage::fake('public');
        Http::preventStrayRequests();
        $this->dir = sys_get_temp_dir().'/mfa-social-albums-'.Str::random(8);
        $this->operator = User::factory()->create();
        $this->artist = $this->makeArtist('Intérprete de prueba', true);
        $this->album = $this->makeAlbum('Disco de prueba');
        config(['social_publication.enabled' => true, 'social_publication.instagram_enabled' => true,
            'social_publication.albums_automatic' => true, 'social_publication.albums_after_id' => $this->album->id - 1,
            'social_publication.operators' => [$this->operator->id], 'social_publication.instagram_poll_seconds' => 0,
            'social_publication.settings_path' => $this->dir.'/settings.json', 'social_publication.credentials_path' => $this->dir.'/meta.json',
            'social_publication.destination' => 'facebook:page:'.self::PAGE, 'social_publication.instagram_destination' => 'instagram:account:'.self::IG]);
        $this->app->instance(SocialPublicationRenderer::class, new class extends SocialPublicationRenderer {
            public function render(array $payload, string $format = 'png'): array
            {
                $bytes = 'fixture-'.$format.'-'.$payload['content_hash']; $hash = hash('sha256', $bytes);
                Storage::disk('local')->put('social-publications/assets/'.$hash.($format === 'jpeg' ? '.jpg' : '.png'), $bytes);
                return ($format === 'jpeg' ? ['asset_url' => route('social-publications.asset', ['hash' => $hash])] : [])
                    + ['asset_reference' => 'sha256:'.$hash, 'asset_sha256' => $hash, 'asset_base64' => base64_encode($bytes),
                        'mime_type' => $format === 'jpeg' ? 'image/jpeg' : 'image/png', 'width' => 1080, 'height' => 1350];
            }
        });
        app(MetaCredentialStore::class)->put('facebook', self::TOKEN, null);
        app(SocialPublicationSettings::class)->update(['executor' => 'laravel', 'alert_email' => 'alertas@example.com']);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir.'/*') ?: [] as $file) { @unlink($file); }
        @rmdir($this->dir);
        parent::tearDown();
    }

    private function makeArtist(string $name, bool $active): Interprete
    {
        return Interprete::create(['interprete' => $name, 'slug' => 'int-'.Str::uuid(), 'biografia' => 'Bio', 'estado' => $active ? 1 : 0, 'user_id' => $this->operator->id]);
    }

    private function makeAlbum(string $name, array $attributes = [], bool $cover = true): Album
    {
        $foto = null;
        if ($cover) {
            $foto = 'cover-'.Str::random(8).'.jpg';
            Storage::disk('public')->put('albunes/'.$foto, $this->jpeg(255, 0, 0));
        }
        return Album::create(array_merge(['album' => $name, 'slug' => 'd-'.Str::uuid(), 'anio' => '2023', 'album_type' => 'album',
            'interprete_id' => $this->artist->id, 'user_id' => $this->operator->id, 'estado' => 1, 'visitas' => 0, 'foto' => $foto], $attributes));
    }

    private function jpeg(int $r, int $g, int $b, int $w = 500, int $h = 500): string
    {
        $image = imagecreatetruecolor($w, $h);
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));
        ob_start(); imagejpeg($image, null, 90); imagedestroy($image);
        return (string) ob_get_clean();
    }

    private function fakeMeta(): void
    {
        Http::fake([
            'graph.facebook.com/v27.0/'.self::PAGE.'/photos' => Http::response(['id' => '111', 'post_id' => self::PAGE.'_555001'], 200),
            'graph.facebook.com/v27.0/'.self::IG.'/media_publish' => Http::response(['id' => '17900000002'], 200),
            'graph.facebook.com/v27.0/'.self::IG.'/media' => Http::response(['id' => '17900000001'], 200),
            'graph.facebook.com/v27.0/17900000001*' => Http::response(['id' => '17900000001', 'status_code' => 'FINISHED'], 200),
            'graph.facebook.com/v27.0/17900000002*' => Http::response(['id' => '17900000002', 'permalink' => 'https://www.instagram.com/p/AbC123_x/', 'media_type' => 'IMAGE'], 200),
        ]);
    }

    private function target(string $provider, int $id): ?PublicationTarget
    {
        return PublicationTarget::where('provider', $provider)->where('ledger_content_kind', 'albums')->where('ledger_content_id', $id)->first();
    }

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');
        $admin = User::factory()->create(); $admin->assignRole('administrador');
        return $admin;
    }

    private function queueIds(string $provider = 'facebook'): array
    {
        return app(SocialPublicationExecutor::class)->queue('albums', $provider)->pluck('albunes.id')->all();
    }

    public function test_queue_takes_active_albums_with_cover_in_id_order(): void
    {
        $second = $this->makeAlbum('Segundo disco');
        $inactive = $this->makeAlbum('Disco inactivo', ['estado' => 0]);
        $noCover = $this->makeAlbum('Disco sin portada', [], false);
        $hiddenArtist = $this->makeArtist('Intérprete inactivo', false);
        $ofHiddenArtist = $this->makeAlbum('Disco de intérprete inactivo', ['interprete_id' => $hiddenArtist->id]);

        $ids = $this->queueIds();
        $this->assertSame([$this->album->id, $second->id], $ids);
        $this->assertNotContains($inactive->id, $ids);
        $this->assertNotContains($noCover->id, $ids);
        $this->assertNotContains($ofHiddenArtist->id, $ids);
        $this->assertSame([], $this->queueIds('instagram'), 'Instagram solo ofrece lo que ya salió en Facebook.');
    }

    public function test_albums_are_off_by_default_in_the_schedule(): void
    {
        $this->fakeMeta();
        $this->travelTo(Carbon::parse('2026-11-23 20:00', 'America/Argentina/Buenos_Aires'));
        $this->assertFalse(app(SocialPublicationSettings::class)->get('schedules.albums.facebook.enabled'));
        $this->artisan('social-publications:run --due')->assertSuccessful();
        Http::assertNothingSent();
        $this->assertNull($this->target('facebook', $this->album->id));
    }

    public function test_scheduled_run_publishes_album_on_facebook_then_instagram_once(): void
    {
        app(SocialPublicationSettings::class)->update(['schedules' => ['albums' => [
            'facebook' => ['enabled' => true, 'time' => '20:00'], 'instagram' => ['enabled' => true, 'time' => '20:00']]]]);
        $this->fakeMeta();
        $this->travelTo(Carbon::parse('2026-11-23 20:00', 'America/Argentina/Buenos_Aires'));
        $this->artisan('social-publications:run --due')->assertSuccessful();

        $fb = $this->target('facebook', $this->album->id);
        $this->assertSame('success', $fb->status);
        $this->assertSame('mfa-discos-v4', $fb->template_id);
        $payload = $fb->payload_snapshot_json;
        $this->assertStringStartsWith('Disco de prueba — Intérprete de prueba (2023)', $payload['copy']);
        $this->assertStringContainsString('/'.$this->artist->slug.'/discografia/'.$this->album->slug, $payload['url']);
        $this->assertSame('albunes/'.$this->album->foto, $payload['cover_path']);
        $this->assertSame('success', $this->target('instagram', $this->album->id)->status);

        $sent = count(Http::recorded());
        $this->travelTo(Carbon::parse('2026-11-23 20:05', 'America/Argentina/Buenos_Aires'));
        $this->artisan('social-publications:run --due')->assertSuccessful();
        $this->assertCount($sent, Http::recorded(), 'El mismo horario no se repite en el día.');
        $this->assertNotContains($this->album->id, $this->queueIds(), 'Un disco publicado no vuelve a la cola.');
    }

    public function test_manual_send_of_a_later_album_keeps_earlier_ones_in_queue(): void
    {
        $second = $this->makeAlbum('Segundo disco');
        $this->fakeMeta();
        $this->actingAs($this->admin())->post('/admin/social-publications/publish', ['content_kind' => 'albums', 'network' => 'both',
            'content_id' => $second->id, 'confirmed' => '1'])->assertRedirect()->assertSessionHas('success');
        $this->assertSame('success', $this->target('facebook', $second->id)->status);
        $this->assertSame('success', $this->target('instagram', $second->id)->status);
        $this->assertNull($this->target('facebook', $this->album->id));
        $this->assertSame($this->album->id, $this->queueIds()[0]);
    }

    public function test_cover_change_changes_the_content_hash(): void
    {
        $queue = app(AutomaticAlbumPublication::class);
        $ledger = new SocialPublicationLedgerService('facebook');
        $before = $ledger->snapshot($queue->eligibility($this->album), $this->album);
        Storage::disk('public')->put('albunes/'.$this->album->foto, $this->jpeg(0, 0, 255));
        $after = $ledger->snapshot($queue->eligibility($this->album), $this->album);
        $this->assertNotSame($before['cover_sha256'], $after['cover_sha256']);
        $this->assertNotSame($before['content_hash'], $after['content_hash']);
    }

    public function test_snapshot_rejects_album_of_inactive_artist(): void
    {
        $this->artist->update(['estado' => 0]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new SocialPublicationLedgerService('facebook'))->snapshot(app(AutomaticAlbumPublication::class)->eligibility($this->album), $this->album->fresh());
    }

    public function test_panel_lists_albums_in_manual_queue_and_schedule(): void
    {
        $this->actingAs($this->admin())->get('/admin/social-publications?tab=manual&m_kind=albums&m_network=facebook')->assertOk()
            ->assertSee('Disco de prueba')->assertSee('(2023)', false)->assertSee('siguiente en la cola');
        $this->get('/admin/social-publications?tab=config')->assertOk()->assertSee('Discos');
    }

    public function test_settings_form_without_albums_keeps_their_schedule(): void
    {
        app(SocialPublicationSettings::class)->update(['schedules' => ['albums' => ['facebook' => ['enabled' => true, 'time' => '21:15']]]]);
        $this->actingAs($this->admin())->post('/admin/social-publications/settings', ['executor' => 'laravel',
            'schedules' => ['news' => ['facebook' => ['enabled' => '1', 'time' => '11:00', 'days' => ['1']], 'instagram' => ['enabled' => '0', 'time' => '11:00']]]])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('21:15', app(SocialPublicationSettings::class)->get('schedules.albums.facebook.time'));
    }

    public function test_real_render_places_the_cover_and_falls_back_without_it(): void
    {
        if (!is_readable(config('social_publication.font_regular')) || !is_readable(config('social_publication.font_bold'))) {
            $this->markTestSkipped('El render requiere fuentes editoriales configuradas; no se redistribuyen fuentes del sistema en CI.');
        }
        $renderer = new SocialPublicationRenderer();
        $payload = (new SocialPublicationLedgerService('facebook'))->snapshot(app(AutomaticAlbumPublication::class)->eligibility($this->album), $this->album);
        $asset = $renderer->render($payload, 'png');
        $image = imagecreatefromstring(base64_decode($asset['asset_base64']));
        $this->assertSame([1080, 1350], [imagesx($image), imagesy($image)]);
        $rgb = imagecolorat($image, 300, 600);
        $this->assertGreaterThan(200, ($rgb >> 16) & 0xFF, 'La portada (roja) ocupa el bloque izquierdo.');

        $withoutCover = ['cover_path' => null, 'cover_sha256' => null] + $payload;
        $plain = imagecreatefromstring(base64_decode($renderer->render($withoutCover, 'png')['asset_base64']));
        $this->assertLessThan(60, (imagecolorat($plain, 300, 700) >> 16) & 0xFF, 'Sin portada queda el fondo oscuro de la v4.');
    }
}
