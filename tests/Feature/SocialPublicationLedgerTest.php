<?php

namespace Tests\Feature;

use App\Models\{News, Event, PublicationAttempt, PublicationTarget, SocialAccount, SocialPublicationEligibility, User};
use App\Services\Publication\{PublicationPayloadSanitizer, SocialPublicationLedgerService, SocialPublicationGuard};
use App\Jobs\Publication\PublishToProviderJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{DB, Http, Queue};
use Illuminate\Support\Str;
use Spatie\Permission\Models\{Permission, Role};
use Tests\TestCase;

class SocialPublicationLedgerTest extends TestCase
{
    use DatabaseTransactions;
    private User $actor;
    private News $news;
    private SocialPublicationLedgerService $ledger;

    protected function setUp(): void
    {
        parent::setUp();
        if (!getenv('CI')) {
            $this->assertSame('mfa_test', DB::connection()->getDatabaseName(), 'Los tests locales requieren la base existente mfa_test.');
        }
        Queue::fake(); Http::fake(); Http::preventStrayRequests();
        config(['social_publication.enabled' => true]);
        $this->actor = User::factory()->create();
        $this->actingAs($this->actor);
        $this->news = News::create(['title' => 'Prueba editorial', 'slug' => 'prueba-'.Str::uuid(), 'body' => 'Texto interno que no se exporta', 'excerpt' => 'Resumen público', 'editorial_status' => 'published', 'publication_mode' => 'portal_only', 'created_by' => $this->actor->id]);
        $this->ledger = app(SocialPublicationLedgerService::class);
    }

    private function enabled(): PublicationTarget { return $this->ledger->enable('news', $this->news->id, true, true, $this->actor->id); }
    private function claim(): array { $this->enabled(); return $this->ledger->claim('news', $this->news->id, (string) Str::uuid(), 'test-execution'); }
    private function begin(array $claim): array
    {
        $data = ['attempt_id' => $claim['attempt_id'], 'lease' => $claim['lease'], 'content_hash' => $claim['payload']['content_hash'], 'asset_reference' => 'https://mifolkloreargentino.com/fixtures/approved.png', 'asset_sha256' => str_repeat('a', 64)];
        $this->ledger->begin($claim['target_id'], $data);
        return $data;
    }
    private function resultData(array $claim, string $outcome = 'success'): array
    {
        return ['attempt_id' => $claim['attempt_id'], 'lease' => $claim['lease'], 'outcome' => $outcome] + ($outcome === 'success' ? ['external_post_id' => '145931542128207_123456'] : []);
    }

    public function test_no_eligibility_is_created_by_default(): void
    {
        $this->assertFalse(SocialPublicationEligibility::where('content_id', $this->news->id)->exists());
        Http::assertNothingSent(); Queue::assertNothingPushed();
    }
    public function test_portal_only_requires_explicit_override(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->enable('news', $this->news->id, true, false, $this->actor->id);
    }
    public function test_enable_audits_without_dispatch_and_does_not_change_mode(): void
    {
        $target = $this->enabled();
        $this->assertSame('n8n', $target->executor);
        $this->assertSame('portal_only', $this->news->fresh()->publication_mode);
        $this->assertDatabaseHas('audit_logs', ['entity_type' => 'social_publication_eligibility', 'action' => 'enabled']);
        Queue::assertNothingPushed(); Http::assertNothingSent();
    }
    public function test_claim_replay_returns_same_attempt_without_secret(): void
    {
        $this->enabled(); $key = (string) Str::uuid();
        $a = $this->ledger->claim('news', $this->news->id, $key, null);
        $b = $this->ledger->claim('news', $this->news->id, $key, null);
        $this->assertSame($a['attempt_id'], $b['attempt_id']); $this->assertArrayNotHasKey('lease', $b);
        $this->assertNotSame($a['lease'], PublicationTarget::find($a['target_id'])->lease_token_hash);
        $this->assertSame(0, PublicationTarget::find($a['target_id'])->attempts_count);
    }
    public function test_second_claim_is_excluded(): void
    {
        $this->claim();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->claim('news', $this->news->id, (string) Str::uuid(), null);
    }
    public function test_expired_reservation_can_be_replaced_without_counting_send(): void
    {
        $a = $this->claim(); $this->travel(11)->minutes();
        $b = $this->ledger->claim('news', $this->news->id, (string) Str::uuid(), null);
        $this->assertNotSame($a['attempt_id'], $b['attempt_id']);
        $this->assertSame('cancelled', PublicationAttempt::find($a['attempt_id'])->status);
        $this->assertSame(0, PublicationTarget::find($a['target_id'])->attempts_count);
    }
    public function test_begin_cannot_be_replayed(): void
    {
        $a = $this->claim(); $data = $this->begin($a);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->begin($a['target_id'], $data);
    }
    public function test_edited_snapshot_rejected_before_begin(): void
    {
        $a = $this->claim(); $this->news->update(['title' => 'Edición concurrente']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->begin($a);
    }
    public function test_frozen_snapshot_excludes_body_and_private_fields(): void
    {
        $a = $this->claim(); $this->begin($a); $this->news->update(['title' => 'Otro título']);
        $snapshot = PublicationTarget::find($a['target_id'])->payload_snapshot_json;
        $this->assertSame('Prueba editorial', $snapshot['title']);
        foreach (['body','created_by','creator','access_token'] as $key) { $this->assertArrayNotHasKey($key, $snapshot); }
    }
    public function test_success_callback_is_idempotent_and_monotonic(): void
    {
        $a = $this->claim(); $this->begin($a); $data = $this->resultData($a);
        $first = $this->ledger->result($a['target_id'], $data); $this->travel(1)->minutes();
        $second = $this->ledger->result($a['target_id'], $data);
        $this->assertEquals($first['published_at'], $second['published_at']);
        $this->assertSame('success', $second['status']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->result($a['target_id'], $this->resultData($a, 'definitive_failure'));
    }
    public function test_uncertain_accepts_same_attempt_late_success(): void
    {
        $a = $this->claim(); $this->begin($a);
        $this->ledger->result($a['target_id'], $this->resultData($a, 'uncertain'));
        $this->assertSame('success', $this->ledger->result($a['target_id'], $this->resultData($a))['status']);
    }
    public function test_expired_processing_becomes_uncertain_without_retry(): void
    {
        $a = $this->claim(); $this->begin($a); $this->travel(11)->minutes();
        $target = PublicationTarget::find($a['target_id']); $this->ledger->expireLocked($target);
        $this->assertSame('uncertain', $target->fresh()->status);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->claim('news', $this->news->id, (string) Str::uuid(), null);
    }
    public function test_definitive_failure_retries_only_after_backoff(): void
    {
        $a = $this->claim(); $this->begin($a); $this->ledger->result($a['target_id'], $this->resultData($a, 'definitive_failure'));
        $this->travel(61)->seconds(); $b = $this->ledger->claim('news', $this->news->id, (string) Str::uuid(), null);
        $this->assertNotSame($a['attempt_id'], $b['attempt_id']);
    }
    public function test_disabling_and_deleting_user_preserve_success(): void
    {
        $a = $this->claim(); $this->begin($a); $this->ledger->result($a['target_id'], $this->resultData($a));
        $this->ledger->enable('news', $this->news->id, false, false, $this->actor->id);
        $this->actor->delete(); $target = PublicationTarget::findOrFail($a['target_id']);
        $this->assertSame('success', $target->status); $this->assertNull($target->request->requested_by);
    }
    public function test_managed_job_never_calls_provider(): void
    {
        $target = $this->enabled(); (new PublishToProviderJob($target->id))->handle();
        $this->assertSame('pending', $target->fresh()->status); Http::assertNothingSent();
    }
    public function test_duplicate_social_accounts_are_guarded_by_external_identity(): void
    {
        $target = $this->enabled();
        foreach ([1,2] as $number) {
            $account = SocialAccount::create(['owner_type' => User::class, 'owner_id' => $this->actor->id, 'provider' => 'facebook', 'account_name' => 'Fixture '.$number, 'account_external_id' => '145931542128207', 'status' => 'active', 'token_encrypted' => 'fixture-not-a-real-token']);
            $legacy = $target->request->targets()->create(['provider' => 'facebook', 'social_account_id' => $account->id, 'status' => 'pending']);
            $this->assertTrue(app(SocialPublicationGuard::class)->managed($legacy));
            (new PublishToProviderJob($legacy->id))->handle();
        }
        Http::assertNothingSent();
    }
    public function test_redaction_removes_secrets_recursively(): void
    {
        $safe = PublicationPayloadSanitizer::clean(['access_token' => 'fixture', 'nested' => ['Authorization' => 'fixture', 'url' => 'https://example.test/?access_token=fixture&x=1']]);
        $this->assertStringNotContainsString('fixture', json_encode($safe));
    }
    private function operatorToken(array $abilities): string
    {
        config(['social_publication.operators' => [$this->actor->id]]);
        foreach (['social-publications:read','social-publications:execute'] as $permission) {
            Permission::findOrCreate($permission, 'web'); $this->actor->givePermissionTo($permission);
        }
        auth()->forgetGuards();
        return $this->actor->createToken('fixture', $abilities)->plainTextToken;
    }
    public function test_wildcard_token_is_rejected(): void
    {
        $token = $this->operatorToken(['*']);
        $this->withToken($token)->getJson('/api/v1/social-publications/candidates')->assertForbidden();
    }
    public function test_scoped_operator_gets_candidates_and_pause(): void
    {
        $this->enabled(); $token = $this->operatorToken(['social-publications:read','social-publications:execute']);
        $this->withToken($token)->getJson('/api/v1/social-publications/candidates')->assertOk()->assertJsonPath('data.0.title', 'Prueba editorial')->assertJsonMissingPath('data.0.body');
        config(['social_publication.enabled' => false]);
        $this->withToken($token)->getJson('/api/v1/social-publications/candidates')->assertStatus(503);
    }
    public function test_backoffice_requires_admin(): void
    {
        $this->get('/admin/social-publications')->assertForbidden();
        Role::findOrCreate('administrador', 'web'); $this->actor->assignRole('administrador');
        $this->get('/admin/social-publications')->assertOk()->assertSee('Publicaciones en redes');
    }

    public function test_api_claim_begin_and_callback_work_while_paused(): void
    {
        $this->enabled(); $token = $this->operatorToken(['social-publications:read','social-publications:execute']);
        $body = ['content_kind' => 'news', 'content_id' => $this->news->id, 'provider' => 'facebook', 'destination_key' => config('social_publication.destination')];
        $claim = $this->withToken($token)->withHeader('Idempotency-Key', (string) Str::uuid())->postJson('/api/v1/social-publications/claims', $body)->assertCreated()->json();
        $this->withToken($token)->postJson('/api/v1/social-publications/'.$claim['target_id'].'/begin', ['attempt_id' => $claim['attempt_id'], 'lease' => $claim['lease'],
            'content_hash' => $claim['payload']['content_hash'], 'asset_reference' => 'https://mifolkloreargentino.com/fixtures/approved.png', 'asset_sha256' => str_repeat('b',64)])->assertOk();
        config(['social_publication.enabled' => false]);
        $this->withToken($token)->postJson('/api/v1/social-publications/'.$claim['target_id'].'/result', $this->resultData($claim))->assertOk()->assertJsonPath('status','success');
        $this->withToken($token)->getJson('/api/v1/social-publications/'.$claim['target_id'])->assertOk()->assertJsonMissingPath('lease_token_hash');
        Http::assertNothingSent();
    }
    public function test_api_rejects_bad_uuid_destination_and_missing_asset(): void
    {
        $this->enabled(); $token = $this->operatorToken(['social-publications:read','social-publications:execute']);
        $body = ['content_kind' => 'news', 'content_id' => $this->news->id, 'provider' => 'facebook', 'destination_key' => config('social_publication.destination')];
        $this->withToken($token)->postJson('/api/v1/social-publications/claims', $body)->assertUnprocessable();
        $this->withToken($token)->getJson('/api/v1/social-publications/candidates?destination_key=facebook:page:999')->assertForbidden();
        $this->withToken($token)->postJson('/api/v1/social-publications/123/begin', [])->assertUnprocessable();
        $this->withToken($token)->getJson('/api/v1/social-publications/999999999')->assertNotFound();
    }
    public function test_cookie_only_and_anonymous_requests_are_denied(): void
    {
        $this->getJson('/api/v1/social-publications/candidates')->assertForbidden();
        auth()->forgetGuards();
        $this->getJson('/api/v1/social-publications/candidates')->assertUnauthorized();
    }
    public function test_asset_with_credentials_is_rejected(): void
    {
        $a = $this->claim();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->begin($a['target_id'], ['attempt_id' => $a['attempt_id'], 'lease' => $a['lease'], 'content_hash' => $a['payload']['content_hash'],
            'asset_reference' => 'https://fixture:fixture@mifolkloreargentino.com/asset.png', 'asset_sha256' => str_repeat('a',64)]);
    }
    public function test_event_expiry_and_template_mapping(): void
    {
        $event = Event::create(['title' => 'Evento de prueba', 'slug' => 'evento-'.Str::uuid(), 'editorial_status' => 'published', 'publication_mode' => 'portal_only', 'start_at' => now()->addDay(), 'status' => 'active']);
        $this->ledger->enable('events', $event->id, true, true, $this->actor->id);
        $a = $this->ledger->claim('events', $event->id, (string) Str::uuid(), null);
        $this->assertSame('mfa-agenda-v4', $a['payload']['template_id']);
        $event->update(['start_at' => now()->subDay()]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->snapshot(SocialPublicationEligibility::where('content_kind','events')->where('content_id',$event->id)->firstOrFail());
    }
    public function test_future_publication_date_is_not_eligible(): void
    {
        $this->news->update(['published_at' => now()->addDay()]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->enabled();
    }
    public function test_success_cannot_be_reserved_again_even_after_edit(): void
    {
        $a = $this->claim(); $this->begin($a); $this->ledger->result($a['target_id'], $this->resultData($a));
        $this->news->update(['title'=>'Título modificado']); $this->enabled();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->claim('news', $this->news->id, (string) Str::uuid(), null);
    }
    public function test_sanitized_connector_attempt_does_not_store_token(): void
    {
        $target = $this->enabled(); $target->update(['executor'=>'laravel','provider'=>'native_portal']);
        $connector = new class extends \App\Services\Connectors\BaseConnector {
            public function publish(PublicationTarget $target): array {
                $attempt = $this->recordAttempt($target, ['access_token'=>'fixture-secret','message'=>'Texto público'], ['success'=>true,'response'=>['nested'=>['Authorization'=>'fixture-secret']]]);
                return $attempt->toArray();
            }
        };
        $this->assertStringNotContainsString('fixture-secret', json_encode($connector->publish($target)));
    }
    public function test_manual_resolution_is_audited_and_blocks_late_contradiction(): void
    {
        $a = $this->claim(); $this->begin($a); $this->ledger->result($a['target_id'], $this->resultData($a,'uncertain'));
        Role::findOrCreate('administrador','web'); $this->actor->assignRole('administrador');
        $this->post('/admin/social-publications/'.$a['target_id'].'/resolve', ['action'=>'not_published','confirmed'=>'1','reason'=>'Verificación manual de prueba sin publicación'])->assertRedirect();
        $this->assertDatabaseHas('audit_logs',['entity_type'=>'publication_target','entity_id'=>$a['target_id'],'action'=>'manual_resolution']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->result($a['target_id'], $this->resultData($a));
    }
    public function test_candidate_query_count_is_bounded_for_more_contents(): void
    {
        $this->enabled(); $token = $this->operatorToken(['social-publications:read']);
        $this->withToken($token)->getJson('/api/v1/social-publications/candidates')->assertOk();
        DB::enableQueryLog(); DB::flushQueryLog();
        $this->withToken($token)->getJson('/api/v1/social-publications/candidates')->assertOk();
        $one = count(DB::getQueryLog()); DB::disableQueryLog();
        for ($i=0; $i<5; $i++) {
            $news = News::create(['title'=>'Otra noticia '.$i,'slug'=>'otra-'.Str::uuid(),'body'=>'Contenido de prueba','editorial_status'=>'published','publication_mode'=>'portal_only']);
            $this->ledger->enable('news',$news->id,true,true,$this->actor->id);
        }
        DB::enableQueryLog(); DB::flushQueryLog();
        $this->withToken($token)->getJson('/api/v1/social-publications/candidates')->assertOk()->assertJsonCount(6,'data');
        $many = count(DB::getQueryLog()); DB::disableQueryLog();
        $this->assertLessThanOrEqual($one+1,$many, "Consultas: {$one} para 1, {$many} para 6");
    }

    public function test_renderer_produces_private_png_and_begin_checks_its_digest(): void
    {
        $this->requireEditorialFonts();
        \Illuminate\Support\Facades\Storage::fake('local');
        $a = $this->claim(); $asset = $this->ledger->render($a['target_id'], ['attempt_id'=>$a['attempt_id'],'lease'=>$a['lease']]);
        $bytes = base64_decode($asset['asset_base64'], true);
        $this->assertSame($asset['asset_sha256'], hash('sha256',$bytes));
        $size = getimagesizefromstring($bytes); $this->assertSame([1080,1350],[$size[0],$size[1]]);
        $this->assertSame('image/png',$size['mime']);
        $result = $this->ledger->begin($a['target_id'], ['attempt_id'=>$a['attempt_id'],'lease'=>$a['lease'],'content_hash'=>$a['payload']['content_hash'],
            'asset_reference'=>$asset['asset_reference'],'asset_sha256'=>$asset['asset_sha256']]);
        $this->assertTrue($result['may_send']);
    }
    public function test_renderer_rejects_overflow_and_does_not_truncate_title(): void
    {
        $this->requireEditorialFonts();
        $a=$this->claim(); $payload=$a['payload']; $payload['title']=str_repeat('título ',180);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(\App\Services\Publication\SocialPublicationRenderer::class)->render($payload);
    }
    public function test_digest_must_be_from_this_reservation(): void
    {
        $this->requireEditorialFonts();
        \Illuminate\Support\Facades\Storage::fake('local');
        $a=$this->claim(); $asset=$this->ledger->render($a['target_id'], ['attempt_id'=>$a['attempt_id'],'lease'=>$a['lease']]);
        \Illuminate\Support\Facades\Storage::disk('local')->put('social-publications/assets/'.$asset['asset_sha256'].'.png','changed fixture');
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->ledger->begin($a['target_id'], ['attempt_id'=>$a['attempt_id'],'lease'=>$a['lease'],'content_hash'=>$a['payload']['content_hash'],
            'asset_reference'=>$asset['asset_reference'],'asset_sha256'=>$asset['asset_sha256']]);
    }
    public function test_render_api_requires_lease_and_respects_pause(): void
    {
        $this->requireEditorialFonts();
        \Illuminate\Support\Facades\Storage::fake('local');
        $a=$this->claim(); $token=$this->operatorToken(['social-publications:execute']);
        $this->withToken($token)->postJson('/api/v1/social-publications/'.$a['target_id'].'/render', ['attempt_id'=>$a['attempt_id'],'lease'=>$a['lease']])
            ->assertOk()->assertJsonPath('mime_type','image/png')->assertJsonMissingPath('lease');
        config(['social_publication.enabled'=>false]);
        $this->withToken($token)->postJson('/api/v1/social-publications/'.$a['target_id'].'/render', ['attempt_id'=>$a['attempt_id'],'lease'=>$a['lease']])->assertStatus(503);
    }

    private function requireEditorialFonts(): void
    {
        if (!is_readable(config('social_publication.font_regular')) || !is_readable(config('social_publication.font_bold'))) {
            $this->markTestSkipped('El render requiere fuentes editoriales configuradas; no se redistribuyen fuentes del sistema en CI.');
        }
    }
}
