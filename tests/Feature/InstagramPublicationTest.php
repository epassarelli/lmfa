<?php

namespace Tests\Feature;

use App\Models\{News, PublicationTarget, PublicationAttempt, User};
use App\Services\Publication\SocialPublicationLedgerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{DB, Http, Queue, Storage};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InstagramPublicationTest extends TestCase
{
    use DatabaseTransactions;
    private User $operator;
    private News $news;
    private string $token;
    private string $base = '/api/v1/social-publications/instagram';

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('mfa_test', DB::connection()->getDatabaseName());
        Http::fake(); Http::preventStrayRequests(); Queue::fake();
        $this->operator = User::factory()->create();
        foreach (['social-publications:read', 'social-publications:execute'] as $ability) {
            $this->operator->givePermissionTo(Permission::findOrCreate($ability, 'web'));
        }
        $this->token = $this->operator->createToken('fixture-instagram', ['social-publications:read', 'social-publications:execute'])->plainTextToken;
        $this->news = News::create(['title' => 'Prueba de Instagram', 'slug' => 'ig-'.Str::uuid(), 'body' => 'Interno', 'excerpt' => 'Resumen', 'editorial_status' => 'published', 'publication_mode' => 'portal_only']);
        config(['social_publication.enabled' => true, 'social_publication.instagram_enabled' => true, 'social_publication.news_automatic' => true,
            'social_publication.instagram_after_id' => $this->news->id - 1, 'social_publication.news_after_id' => $this->news->id - 1,
            'social_publication.operators' => [$this->operator->id]]);
    }

    private function facebook(): PublicationTarget
    {
        $target = app(SocialPublicationLedgerService::class)->enable('news', $this->news->id, true, true, $this->operator->id);
        $target->update(['status' => 'success', 'external_post_id' => '145931542128207_123456', 'attempts_count' => 1]);
        return $target;
    }

    private function claim(): array
    {
        return $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())->postJson($this->base.'/claims', [
            'provider' => 'instagram', 'destination_key' => config('social_publication.instagram_destination'), 'content_kind' => 'news', 'content_id' => $this->news->id,
        ])->assertCreated()->json();
    }

    private function processing(): array
    {
        $this->facebook(); $c = $this->claim();
        Storage::fake('local');
        $bytes = 'fixture-content-addressed-image'; $hash = hash('sha256', $bytes);
        Storage::disk('local')->put('social-publications/assets/'.$hash.'.jpg', $bytes);
        PublicationAttempt::findOrFail($c['attempt_id'])->update(['request_payload_json' => ['asset_sha256' => $hash]]);
        $data = ['attempt_id' => $c['attempt_id'], 'lease' => $c['lease']];
        $this->withToken($this->token)->postJson($this->base.'/'.$c['target_id'].'/begin', $data + [
            'content_hash' => $c['payload']['content_hash'], 'asset_reference' => 'sha256:'.$hash, 'asset_sha256' => $hash,
        ])->assertOk()->assertJsonPath('may_send', true);
        return $c;
    }

    public function test_queue_requires_facebook_success_and_is_read_only(): void
    {
        $this->withToken($this->token)->getJson($this->base.'/candidates')->assertOk()->assertJsonCount(0, 'data');
        $this->facebook(); $count = PublicationTarget::count();
        $this->withToken($this->token)->getJson($this->base.'/candidates')->assertOk()->assertJsonPath('data.0.content_id', $this->news->id)->assertJsonPath('data.0.provider', 'instagram');
        $this->assertSame($count, PublicationTarget::count());
        Http::assertNothingSent(); Queue::assertNothingPushed();
    }

    public function test_independent_target_and_facebook_routes_cannot_access_instagram(): void
    {
        $fb = $this->facebook(); $c = $this->claim();
        $this->assertNotSame($fb->id, $c['target_id']);
        $this->assertSame('success', $fb->fresh()->status);
        $this->withToken($this->token)->getJson('/api/v1/social-publications/'.$c['target_id'])->assertNotFound();
        $this->withToken($this->token)->getJson($this->base.'/'.$fb->id)->assertNotFound();
        $this->withToken($this->token)->getJson($this->base.'/candidates')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_unpublished_facebook_cannot_be_claimed_and_wrong_destination_is_rejected(): void
    {
        $payload = ['provider' => 'instagram', 'destination_key' => config('social_publication.instagram_destination'), 'content_kind' => 'news', 'content_id' => $this->news->id];
        $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())->postJson($this->base.'/claims', $payload)->assertStatus(409);
        $payload['destination_key'] = config('social_publication.destination');
        $this->withToken($this->token)->postJson($this->base.'/claims', $payload)->assertForbidden();
    }

    public function test_container_rechecks_content_and_cannot_be_replaced(): void
    {
        $c = $this->processing(); $data = ['attempt_id' => $c['attempt_id'], 'lease' => $c['lease'], 'container_id' => '123456789'];
        $url = $this->base.'/'.$c['target_id'].'/container';
        $this->withToken($this->token)->postJson($url, $data)->assertOk();
        $this->withToken($this->token)->postJson($url, $data)->assertOk();
        $this->withToken($this->token)->postJson($url, array_merge($data, ['container_id' => '987654321']))->assertStatus(409);
        $this->news->update(['editorial_status' => 'draft']);
        $this->withToken($this->token)->postJson($url, $data)->assertStatus(409);
    }

    public function test_success_is_idempotent_and_uncertain_is_excluded(): void
    {
        $c = $this->processing(); $data = ['attempt_id' => $c['attempt_id'], 'lease' => $c['lease']];
        $this->withToken($this->token)->postJson($this->base.'/'.$c['target_id'].'/container', $data + ['container_id' => '123456789'])->assertOk();
        $result = $this->base.'/'.$c['target_id'].'/result';
        $this->withToken($this->token)->postJson($result, $data + ['outcome' => 'uncertain'])->assertOk();
        $this->withToken($this->token)->getJson($this->base.'/candidates')->assertOk()->assertJsonCount(0, 'data');
        $success = $data + ['outcome' => 'success', 'external_post_id' => '12345678901234', 'external_url' => 'https://www.instagram.com/p/Fixture123/'];
        $this->withToken($this->token)->postJson($result, $success)->assertOk()->assertJsonPath('status', 'success');
        $this->withToken($this->token)->postJson($result, $success)->assertOk();
        $this->assertSame(1, PublicationTarget::findOrFail($c['target_id'])->attempts_count);
        $this->withToken($this->token)->getJson($this->base.'/candidates')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_success_requires_container_and_real_permalink_shape(): void
    {
        $c = $this->processing(); $url = $this->base.'/'.$c['target_id'].'/result';
        $data = ['attempt_id' => $c['attempt_id'], 'lease' => $c['lease'], 'outcome' => 'success', 'external_post_id' => '123456789', 'external_url' => 'https://www.instagram.com/p/Fixture123/'];
        $this->withToken($this->token)->postJson($url, $data)->assertStatus(409);
        $data['external_url'] = 'https://attacker.example/p/Fixture123/';
        $this->withToken($this->token)->postJson($url, $data)->assertUnprocessable();
    }

    public function test_assets_are_hash_scoped_and_paused_instagram_is_blocked(): void
    {
        Storage::fake('local'); $bytes = 'fixture'; $hash = hash('sha256', $bytes);
        Storage::disk('local')->put('social-publications/assets/'.$hash.'.jpg', $bytes);
        $this->get('/api/v1/social-publication-assets/'.$hash.'.jpg')->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->get('/api/v1/social-publication-assets/'.str_repeat('a', 64).'.jpg')->assertNotFound();
        $this->get('/api/v1/social-publication-assets/not-a-hash.jpg')->assertNotFound();
        config(['social_publication.instagram_enabled' => false]);
        $this->withToken($this->token)->getJson($this->base.'/candidates')->assertStatus(503);
        $this->get('/api/v1/social-publication-assets/'.$hash.'.jpg')->assertNotFound();
    }

    public function test_renderer_creates_a_real_jpeg_with_approved_dimensions(): void
    {
        if (!is_readable(config('social_publication.font_regular'))) { $this->markTestSkipped('Fuente editorial no disponible.'); }
        Storage::fake('local');
        $this->facebook(); $c = $this->claim();
        $asset = $this->withToken($this->token)->postJson($this->base.'/'.$c['target_id'].'/render', ['attempt_id' => $c['attempt_id'], 'lease' => $c['lease']])
            ->assertOk()->assertJsonPath('mime_type', 'image/jpeg')->assertJsonPath('width', 1080)->assertJsonPath('height', 1350)->json();
        $bytes = base64_decode($asset['asset_base64'], true);
        $info = getimagesizefromstring($bytes);
        $this->assertSame(IMAGETYPE_JPEG, $info[2]);
        $this->assertSame($asset['asset_sha256'], hash('sha256', $bytes));
        $this->get('/api/v1/social-publication-assets/'.$asset['asset_sha256'].'.jpg')->assertOk();
    }
}
