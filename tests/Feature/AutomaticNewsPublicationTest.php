<?php

namespace Tests\Feature;

use App\Models\{News, Event, PublicationRequest, PublicationTarget, PublicationAttempt, SocialAccount, SocialPublicationEligibility, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{DB, Http, Queue};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AutomaticNewsPublicationTest extends TestCase
{
    use DatabaseTransactions;
    private User $operator;
    private News $first;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        if (!getenv('CI')) { $this->assertSame('mfa_test', DB::connection()->getDatabaseName()); }
        Http::fake(); Http::preventStrayRequests(); Queue::fake();
        $this->operator = User::factory()->create();
        foreach (['social-publications:read', 'social-publications:execute'] as $ability) {
            $this->operator->givePermissionTo(Permission::findOrCreate($ability, 'web'));
        }
        $this->token = $this->operator->createToken('fixture-auto', ['social-publications:read', 'social-publications:execute'])->plainTextToken;
        $this->first = $this->news();
        config(['social_publication.enabled' => true, 'social_publication.news_automatic' => true,
            'social_publication.news_after_id' => $this->first->id - 1, 'social_publication.operators' => [$this->operator->id]]);
    }

    private function news(array $extra = []): News
    {
        return News::create(array_merge(['title' => 'Noticia automática de prueba', 'slug' => 'auto-'.Str::uuid(),
            'body' => 'Cuerpo que no se exporta', 'excerpt' => 'Resumen público', 'estado' => 1,
            'editorial_status' => 'published', 'publication_mode' => 'portal_only'], $extra));
    }

    private function candidates(int $limit = 50)
    {
        return $this->withToken($this->token)->getJson('/api/v1/social-publications/candidates?content_kind=news&per_page='.$limit)->assertOk();
    }

    private function claim(News $news): array
    {
        return $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())->postJson('/api/v1/social-publications/claims',
            ['content_kind' => 'news', 'content_id' => $news->id, 'provider' => 'facebook', 'destination_key' => config('social_publication.destination')])->assertCreated()->json();
    }

    public function test_candidates_require_no_manual_enable_and_get_does_not_write(): void
    {
        $eligibilities = SocialPublicationEligibility::count(); $targets = PublicationTarget::count();
        $this->news();
        $this->candidates(1)->assertJsonCount(1, 'data')->assertJsonPath('data.0.content_id', $this->first->id)->assertJsonMissingPath('data.0.body');
        $this->assertSame($eligibilities, SocialPublicationEligibility::count());
        $this->assertSame($targets, PublicationTarget::count());
    }

    public function test_claim_bootstraps_audited_target_without_changing_publication_mode(): void
    {
        $claim = $this->claim($this->first);
        $this->assertDatabaseHas('social_publication_eligibilities', ['content_kind' => 'news', 'content_id' => $this->first->id,
            'enabled' => true, 'portal_only_override' => true, 'enabled_by' => $this->operator->id]);
        $this->assertSame('reserved', PublicationTarget::findOrFail($claim['target_id'])->status);
        $this->assertSame('portal_only', $this->first->fresh()->publication_mode);
        $this->assertDatabaseHas('audit_logs', ['entity_type' => 'social_publication_eligibility', 'action' => 'enabled']);
        Http::assertNothingSent(); Queue::assertNothingPushed();
    }

    public function test_floor_excludes_the_already_published_origin_and_older_records(): void
    {
        config(['social_publication.news_after_id' => $this->first->id]);
        $next = $this->news();
        $this->candidates()->assertJsonCount(1, 'data')->assertJsonPath('data.0.content_id', $next->id);
    }

    public function test_draft_inactive_and_future_news_are_not_candidates(): void
    {
        $draft = $this->news(['editorial_status' => 'draft']);
        $inactive = $this->news(); $inactive->update(['estado' => 0]);
        $future = $this->news(['published_at' => now()->addDay()]);
        $this->candidates()->assertJsonCount(1, 'data')->assertJsonPath('data.0.content_id', $this->first->id);
        foreach ([$draft, $inactive, $future] as $news) {
            $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())->postJson('/api/v1/social-publications/claims',
                ['content_kind' => 'news', 'content_id' => $news->id, 'provider' => 'facebook', 'destination_key' => config('social_publication.destination')])->assertNotFound();
        }
    }

    public function test_success_uncertain_and_processing_are_not_repeated(): void
    {
        foreach (['success', 'uncertain', 'processing'] as $i => $status) {
            $news = $i === 0 ? $this->first : $this->news();
            $claim = $this->claim($news);
            PublicationTarget::findOrFail($claim['target_id'])->update(['status' => $status]);
        }
        $next = $this->news();
        $this->candidates()->assertJsonCount(1, 'data')->assertJsonPath('data.0.content_id', $next->id);
    }

    public function test_expired_reservation_is_recoverable_without_a_manual_action(): void
    {
        $first = $this->claim($this->first); $this->travel(11)->minutes();
        $this->candidates(1)->assertJsonPath('data.0.content_id', $this->first->id);
        $next = $this->claim($this->first);
        $this->assertNotSame($first['attempt_id'], $next['attempt_id']);
        $this->assertSame('cancelled', PublicationAttempt::findOrFail($first['attempt_id'])->status);
        $this->assertSame(0, PublicationTarget::findOrFail($next['target_id'])->attempts_count);
    }

    public function test_later_activation_does_not_permanently_skip_an_older_unpublished_news(): void
    {
        $this->first->update(['editorial_status' => 'draft']);
        $second = $this->news();
        $this->candidates(1)->assertJsonPath('data.0.content_id', $second->id);
        $claim = $this->claim($second); PublicationTarget::findOrFail($claim['target_id'])->update(['status' => 'success']);
        $this->first->update(['editorial_status' => 'published']);
        $this->candidates(1)->assertJsonPath('data.0.content_id', $this->first->id);
    }

    public function test_inactivation_after_claim_blocks_begin(): void
    {
        $claim = $this->claim($this->first); $this->first->update(['estado' => 0]);
        $this->withToken($this->token)->postJson('/api/v1/social-publications/'.$claim['target_id'].'/begin',
            ['attempt_id' => $claim['attempt_id'], 'lease' => $claim['lease'], 'content_hash' => $claim['payload']['content_hash'],
                'asset_reference' => 'https://mifolkloreargentino.com/fixture.png', 'asset_sha256' => str_repeat('a', 64)])->assertStatus(409);
        Http::assertNothingSent();
    }

    public function test_legacy_real_page_history_is_excluded_without_backfill(): void
    {
        $account = SocialAccount::create(['owner_type' => User::class, 'owner_id' => $this->operator->id, 'provider' => 'facebook',
            'account_name' => 'Fixture Page', 'account_external_id' => '145931542128207', 'token_encrypted' => 'fixture-not-real', 'status' => 'active']);
        $request = PublicationRequest::create(['content_type' => News::class, 'content_id' => $this->first->id,
            'requested_by' => $this->operator->id, 'mode' => 'portal_social', 'status' => 'completed']);
        $request->targets()->create(['provider' => 'facebook', 'social_account_id' => $account->id, 'status' => 'success']);
        $this->candidates()->assertJsonCount(0, 'data');
    }

    public function test_events_still_require_manual_eligibility(): void
    {
        Event::create(['title' => 'Evento fixture', 'slug' => 'event-'.Str::uuid(), 'editorial_status' => 'published', 'start_at' => now()->addDay()]);
        $this->withToken($this->token)->getJson('/api/v1/social-publications/candidates?content_kind=events')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_uses_the_same_public_state_as_the_portal_despite_stale_legacy_column(): void
    {
        DB::table('news')->where('id', $this->first->id)->update(['estado' => 0]);
        $this->assertTrue($this->first->fresh()->isPublished());
        $this->candidates(1)->assertJsonPath('data.0.content_id', $this->first->id);
        $claim = $this->claim($this->first);
        $this->assertSame('reserved', PublicationTarget::findOrFail($claim['target_id'])->status);
    }

    public function test_active_news_does_not_need_an_old_manual_opt_in_to_be_reenabled(): void
    {
        $first = $this->claim($this->first);
        PublicationAttempt::findOrFail($first['attempt_id'])->update(['status' => 'cancelled']);
        PublicationTarget::findOrFail($first['target_id'])->update(['status' => 'cancelled']);
        SocialPublicationEligibility::where('content_kind', 'news')->where('content_id', $this->first->id)->update(['enabled' => false]);
        $this->candidates(1)->assertJsonPath('data.0.content_id', $this->first->id);
        $second = $this->claim($this->first);
        $this->assertNotSame($first['attempt_id'], $second['attempt_id']);
        $this->assertSame('reserved', PublicationTarget::findOrFail($second['target_id'])->status);
    }

    public function test_safe_failure_respects_backoff_and_three_send_limit(): void
    {
        $claim = $this->claim($this->first);
        $target = PublicationTarget::findOrFail($claim['target_id']);
        $target->update(['status' => 'failed', 'attempts_count' => 1, 'retry_after' => now()->addMinutes(5)]);
        $next = $this->news();
        $this->candidates(1)->assertJsonPath('data.0.content_id', $next->id);
        $this->travel(6)->minutes();
        $this->candidates(1)->assertJsonPath('data.0.content_id', $this->first->id);
        $target->update(['attempts_count' => 3]);
        $this->candidates(1)->assertJsonPath('data.0.content_id', $next->id);
    }

    public function test_query_count_does_not_grow_per_news(): void
    {
        $this->candidates(); DB::enableQueryLog(); DB::flushQueryLog(); $this->candidates();
        $one = count(DB::getQueryLog()); DB::disableQueryLog();
        for ($i=0; $i<5; $i++) { $this->news(); }
        DB::enableQueryLog(); DB::flushQueryLog(); $this->candidates()->assertJsonCount(6, 'data');
        $many = count(DB::getQueryLog()); DB::disableQueryLog();
        $this->assertLessThanOrEqual($one+1, $many);
    }
}
