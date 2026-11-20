<?php

namespace Tests\Feature;

use App\Mail\SocialPublicationAlertMail;
use App\Models\{AuditLog, Event, News, PublicationAttempt, PublicationTarget, User};
use App\Services\Publication\{MetaCredentialStore, SocialPublicationExecutor, SocialPublicationRenderer, SocialPublicationSettings};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Http, Mail, Queue, Storage};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Spec social-publication-laravel-executor: Meta simulado con Http::fake(), sin publicaciones reales. */
class SocialPublicationExecutorTest extends TestCase
{
    use DatabaseTransactions;

    private User $operator;
    private News $news;
    private string $dir;
    private const PAGE = '145931542128207';
    private const IG = '17841400312117345';
    private const TOKEN = 'EAAtest0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJ';

    protected function setUp(): void
    {
        parent::setUp();
        if (!getenv('CI')) { $this->assertSame('mfa_test', DB::connection()->getDatabaseName()); }
        Queue::fake(); Mail::fake(); Storage::fake('local');
        Http::preventStrayRequests();
        $this->dir = sys_get_temp_dir().'/mfa-social-'.Str::random(8);
        $this->operator = User::factory()->create();
        $this->news = $this->makeNews('Primera noticia de prueba');
        config(['social_publication.enabled' => true, 'social_publication.instagram_enabled' => true, 'social_publication.news_automatic' => true,
            'social_publication.news_after_id' => $this->news->id - 1, 'social_publication.instagram_after_id' => $this->news->id - 1,
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

    private function makeNews(string $title): News
    {
        return News::create(['title' => $title, 'slug' => 'exec-'.Str::uuid(), 'body' => 'Interno', 'excerpt' => 'Resumen',
            'editorial_status' => 'published', 'publication_mode' => 'portal_only']);
    }

    private function at(string $time): void
    {
        $this->travelTo(Carbon::parse('2026-11-23 '.$time, 'America/Argentina/Buenos_Aires'));
    }

    private function fakeMeta(array $overrides = []): void
    {
        Http::fake(array_merge([
            'graph.facebook.com/v27.0/'.self::PAGE.'/photos' => Http::response(['id' => '111', 'post_id' => self::PAGE.'_555001'], 200),
            'graph.facebook.com/v27.0/'.self::IG.'/media_publish' => Http::response(['id' => '17900000002'], 200),
            'graph.facebook.com/v27.0/'.self::IG.'/media' => Http::response(['id' => '17900000001'], 200),
            'graph.facebook.com/v27.0/17900000001*' => Http::response(['id' => '17900000001', 'status_code' => 'FINISHED'], 200),
            'graph.facebook.com/v27.0/17900000002*' => Http::response(['id' => '17900000002', 'permalink' => 'https://www.instagram.com/p/AbC123_x/', 'media_type' => 'IMAGE'], 200),
        ], $overrides));
    }

    private function target(string $provider, ?int $id = null): ?PublicationTarget
    {
        return PublicationTarget::where('provider', $provider)->where('ledger_content_kind', 'news')->where('ledger_content_id', $id ?? $this->news->id)->first();
    }

    public function test_with_n8n_as_executor_the_scheduler_never_calls_meta(): void
    {
        app(SocialPublicationSettings::class)->update(['executor' => 'n8n']);
        $this->fakeMeta(); $this->at('11:00');
        $this->artisan('social-publications:run --due')->assertSuccessful();
        Http::assertNothingSent();
        $this->assertNull($this->target('facebook'));
    }

    public function test_scheduled_run_publishes_facebook_then_instagram_once_per_day(): void
    {
        $this->fakeMeta(); $this->at('11:00');
        $this->artisan('social-publications:run --due')->assertSuccessful();

        $fb = $this->target('facebook');
        $this->assertSame('success', $fb->status);
        $this->assertSame(self::PAGE.'_555001', $fb->external_post_id);
        $this->assertSame('portal', $fb->attempts()->first()->executor);
        $ig = $this->target('instagram');
        $this->assertSame('success', $ig->status);
        $this->assertSame('https://www.instagram.com/p/AbC123_x/', $ig->external_url);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), self::PAGE.'/photos') && $r->hasHeader('Authorization', 'Bearer '.self::TOKEN)
            && !str_contains($r->url(), 'access_token'));
        $sent = count(Http::recorded());

        $this->at('11:05');
        $this->artisan('social-publications:run --due')->assertSuccessful();
        $this->assertCount($sent, Http::recorded(), 'El mismo horario no se repite en el día.');
        Mail::assertNothingSent();
        $this->assertSame(2, AuditLog::where('entity_type', 'social_publication_run')->count());
    }

    public function test_slot_outside_window_or_before_time_does_not_run(): void
    {
        $this->fakeMeta();
        $this->at('10:59');
        $this->artisan('social-publications:run --due');
        $this->at('14:30');
        $this->artisan('social-publications:run --due');
        Http::assertNothingSent();
    }

    public function test_slot_runs_only_on_selected_weekdays(): void
    {
        $settings = app(SocialPublicationSettings::class);
        $settings->update(['schedules' => ['news' => ['facebook' => ['days' => [1]], 'instagram' => ['enabled' => false]]]]); // solo lunes
        $this->fakeMeta();
        $this->at('11:00'); // 2026-11-23 es lunes
        $this->artisan('social-publications:run --due');
        $this->assertSame('success', $this->target('facebook')->status);
        $this->assertSame([1], $settings->get('schedules.news.facebook.days'));

        $second = $this->makeNews('Noticia del martes');
        $this->travelTo(Carbon::parse('2026-11-24 11:00', 'America/Argentina/Buenos_Aires'));
        $this->artisan('social-publications:run --due');
        $this->assertNull($this->target('facebook', $second->id), 'El martes no está marcado.');
    }

        public function test_pause_blocks_scheduled_and_manual_runs(): void
    {
        app(SocialPublicationSettings::class)->update(['paused' => true]);
        $this->fakeMeta(); $this->at('11:00');
        $this->artisan('social-publications:run --due');
        $result = app(SocialPublicationExecutor::class)->publish('news', 'facebook', $this->news->id, $this->operator, 'manual');
        $this->assertSame('skipped', $result['status']);
        Http::assertNothingSent();
    }

    public function test_meta_rejection_is_a_safe_failure_with_alert(): void
    {
        $this->fakeMeta(['graph.facebook.com/v27.0/'.self::PAGE.'/photos' => Http::response(['error' => ['code' => 100, 'message' => 'Invalid parameter']], 400)]);
        $result = app(SocialPublicationExecutor::class)->publish('news', 'facebook', null, null, 'console');
        $this->assertSame('failed', $result['status']);
        $target = $this->target('facebook');
        $this->assertSame('META_REJECTED', $target->last_error_code);
        $this->assertNotNull($target->retry_after);
        $this->assertSame('Invalid parameter', $target->attempts()->first()->response_payload_json['meta_message']);
        Mail::assertSent(SocialPublicationAlertMail::class, fn ($m) => $m->hasTo('alertas@example.com'));
    }

    public function test_server_error_is_uncertain_and_never_resent(): void
    {
        $this->fakeMeta(['graph.facebook.com/v27.0/'.self::PAGE.'/photos' => Http::response('boom', 502)]);
        $executor = app(SocialPublicationExecutor::class);
        $this->assertSame('uncertain', $executor->publish('news', 'facebook', null, null, 'console')['status']);
        $this->assertSame('empty', $executor->publish('news', 'facebook', null, null, 'console')['status']);
        Http::assertSentCount(1);
        Mail::assertSent(SocialPublicationAlertMail::class);
    }

    public function test_instagram_container_rejection_never_calls_media_publish(): void
    {
        $this->fakeMeta(['graph.facebook.com/v27.0/'.self::IG.'/media' => Http::response(['error' => ['code' => 9004, 'message' => 'Media download failed']], 400)]);
        $executor = app(SocialPublicationExecutor::class);
        $this->assertSame('success', $executor->publish('news', 'facebook', null, null, 'console')['status']);
        $this->assertSame('failed', $executor->publish('news', 'instagram', null, null, 'console')['status']);
        $this->assertSame('IG_CONTAINER_REJECTED', $this->target('instagram')->last_error_code);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'media_publish'));
    }

    public function test_instagram_unverified_permalink_stays_uncertain(): void
    {
        $this->fakeMeta(['graph.facebook.com/v27.0/17900000002*' => Http::response(['error' => ['message' => 'temporary']], 500)]);
        $executor = app(SocialPublicationExecutor::class);
        $executor->publish('news', 'facebook', null, null, 'console');
        $this->assertSame('uncertain', $executor->publish('news', 'instagram', null, null, 'console')['status']);
        $attempt = $this->target('instagram')->attempts()->first();
        $this->assertSame('17900000002', $attempt->request_payload_json['instagram_observed_media_id']);
    }

    public function test_instagram_requires_facebook_success_first(): void
    {
        $this->fakeMeta();
        $result = app(SocialPublicationExecutor::class)->publish('news', 'instagram', null, null, 'console');
        $this->assertSame('empty', $result['status']);
        Http::assertNothingSent();
    }

    public function test_missing_token_reports_error_without_claiming(): void
    {
        app(MetaCredentialStore::class)->forget('facebook');
        $result = app(SocialPublicationExecutor::class)->publish('news', 'facebook', null, null, 'console');
        $this->assertSame('error', $result['status']);
        $this->assertNull($this->target('facebook'));
        Http::assertNothingSent();
    }

    public function test_events_follow_the_same_automatic_flow(): void
    {
        $event = Event::create(['title' => 'Peña de prueba', 'slug' => 'pena-'.Str::uuid(), 'editorial_status' => 'published',
            'publication_mode' => 'portal_only', 'start_at' => now()->addDays(3), 'status' => 'active']);
        config(['social_publication.events_after_id' => $event->id - 1]);
        $this->fakeMeta();
        $executor = app(SocialPublicationExecutor::class);
        $this->assertSame('success', $executor->publish('events', 'facebook', null, null, 'console')['status']);
        $this->assertSame('success', $executor->publish('events', 'instagram', null, null, 'console')['status']);
        $this->assertSame(['success', 'success'], PublicationTarget::where('ledger_content_kind', 'events')->where('ledger_content_id', $event->id)
            ->orderBy('provider')->pluck('status')->all());
        $this->assertSame('empty', $executor->publish('events', 'facebook', null, null, 'console')['status']);
    }

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');
        $admin = User::factory()->create(); $admin->assignRole('administrador');
        return $admin;
    }

    public function test_manual_send_of_a_later_item_keeps_earlier_ones_in_queue(): void
    {
        $second = $this->makeNews('Segunda noticia de prueba');
        $this->fakeMeta();
        $this->actingAs($this->admin())->post('/admin/social-publications/publish', ['content_kind' => 'news', 'network' => 'both',
            'content_id' => $second->id, 'confirmed' => '1'])->assertRedirect()->assertSessionHas('success');
        $this->assertSame('success', $this->target('facebook', $second->id)->status);
        $this->assertSame('success', $this->target('instagram', $second->id)->status);
        $this->assertNull($this->target('facebook'));
        $this->assertSame($this->news->id, app(SocialPublicationExecutor::class)->queue('news', 'facebook')->value('news.id'));
    }

    public function test_manual_send_requires_confirmation_and_admin(): void
    {
        $this->fakeMeta();
        $this->actingAs($this->operator)->post('/admin/social-publications/publish', ['content_kind' => 'news', 'network' => 'facebook',
            'content_id' => $this->news->id, 'confirmed' => '1'])->assertForbidden();
        $this->actingAs($this->admin())->post('/admin/social-publications/publish', ['content_kind' => 'news', 'network' => 'facebook',
            'content_id' => $this->news->id])->assertSessionHasErrors('confirmed');
        Http::assertNothingSent();
    }

    public function test_panel_shows_tabs_queue_and_never_the_token(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/social-publications')->assertOk()->assertSee('Log')->assertSee('Envío manual')->assertDontSee(self::TOKEN);
        $this->get('/admin/social-publications?tab=manual&m_kind=news&m_network=facebook')->assertOk()
            ->assertSee($this->news->title)->assertSee('siguiente en la cola');
        $this->get('/admin/social-publications?tab=config')->assertOk()->assertSee('terminado en …'.substr(self::TOKEN, -4))->assertDontSee(self::TOKEN);
        $this->assertStringNotContainsString(self::TOKEN, (string) file_get_contents($this->dir.'/meta.json'));
    }

    public function test_settings_update_schedules_and_executor(): void
    {
        $this->actingAs($this->admin())->post('/admin/social-publications/settings', ['executor' => 'n8n', 'paused' => '1', 'alert_email' => 'yo@example.com',
            'schedules' => ['news' => ['facebook' => ['enabled' => '1', 'time' => '09:30', 'days' => ['1', '3', '5']], 'instagram' => ['enabled' => '0', 'time' => '20:00']],
                'events' => ['facebook' => ['enabled' => '1', 'time' => '18:15', 'days' => ['4', '5']], 'instagram' => ['enabled' => '0', 'time' => '18:00']]]])
            ->assertRedirect()->assertSessionHasNoErrors();
        $s = app(SocialPublicationSettings::class)->all();
        $this->assertSame('n8n', $s['executor']);
        $this->assertTrue($s['paused']);
        $this->assertSame(['enabled' => true, 'time' => '09:30', 'days' => [1, 3, 5]], $s['schedules']['news']['facebook']);
        $this->assertSame([4, 5], $s['schedules']['events']['facebook']['days']);
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], $s['schedules']['news']['instagram']['days'], 'Sin días marcados en un horario apagado queda diario.');
        $this->post('/admin/social-publications/settings', ['executor' => 'laravel', 'schedules' => [
            'news' => ['facebook' => ['enabled' => '1', 'time' => '09:30'], 'instagram' => ['enabled' => '0', 'time' => '20:00']],
            'events' => ['facebook' => ['enabled' => '0', 'time' => '18:15'], 'instagram' => ['enabled' => '0', 'time' => '18:00']]]])
            ->assertSessionHasErrors('schedules');
        $this->assertFalse($s['schedules']['news']['instagram']['enabled']);
        $this->post('/admin/social-publications/settings', ['executor' => 'laravel', 'schedules' => ['news' => ['facebook' => ['time' => '25:00']]]])
            ->assertSessionHasErrors();
    }

    public function test_token_store_verifies_against_meta_without_exposing_it(): void
    {
        Http::fake(['graph.facebook.com/v27.0/'.self::PAGE.'*' => Http::response(['id' => self::PAGE, 'name' => 'Mi Folklore Argentino'], 200)]);
        $new = 'EAAnew9876543210zyxwvutsrqponmlkjihgfedcbaZYXWVUTSRQ';
        $this->actingAs($this->admin())->post('/admin/social-publications/token', ['provider' => 'facebook', 'token' => $new])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame($new, app(MetaCredentialStore::class)->token('facebook'));
        $this->assertSame($new, app(MetaCredentialStore::class)->token('instagram'), 'Instagram hereda el token de Facebook.');
        $this->assertTrue(app(MetaCredentialStore::class)->status()['facebook']['check_ok']);
        $this->assertStringNotContainsString($new, json_encode(AuditLog::where('entity_type', 'social_publication_token')->get()));
    }
}
