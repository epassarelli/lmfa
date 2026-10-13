<?php

namespace Tests\Feature;

use App\Jobs\SendNewsletterJob;
use App\Mail\NewsletterEditionMail;
use App\Models\Interprete;
use App\Models\NewsletterEdition;
use App\Models\NewsletterLink;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Services\NewsletterBounceDetector;
use App\Services\NewsletterEditionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NewsletterClicksAndBouncesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $this->admin->assignRole('administrador');
    }

    private function approvedEdition(): NewsletterEdition
    {
        $artist = Interprete::create(['interprete' => 'Artista de clics '.uniqid(), 'slug' => 'clic-'.uniqid(), 'biografia' => '<p>Bio</p>', 'estado' => 1]);
        $artist->forceFill(['created_at' => now()->subWeek()])->save();
        [$start, $end] = NewsletterEditionService::lastWeek();

        $edition = NewsletterEdition::create([
            'edition_key' => 'clicks-'.uniqid(), 'subject' => 'Clics', 'status' => 'draft',
            'period_start' => $start->toDateString(), 'period_end' => $end->toDateString(),
            'selection' => ['artists' => [$artist->id]],
        ]);

        return app(NewsletterEditionService::class)->approve($edition, $this->admin);
    }

    private function subscriber(string $email, string $status = 'active', bool $confirmed = true): NewsletterSubscriber
    {
        return NewsletterSubscriber::create(['email' => $email, 'status' => $status, 'source' => 'sidebar', 'confirmed_at' => $confirmed ? now() : null]);
    }

    // ---------------- Clics ----------------

    public function test_approving_creates_one_trackable_link_per_item_and_the_mail_uses_it(): void
    {
        $edition = $this->approvedEdition();
        $link = $edition->links()->firstOrFail();
        $item = $edition->snapshot['artists'][0];

        $this->assertSame($link->id, $item['link_id']);
        $this->assertSame($item['url'], $link->url);
        $this->assertSame(0, $link->clicks);

        $html = (new NewsletterEditionMail($edition, $this->subscriber('lector@example.test'), $edition->snapshot))->render();
        $this->assertStringContainsString(route('newsletter.click', [$edition->id, $link->id]), $html);
    }

    public function test_a_click_is_counted_and_redirects_to_the_stored_url(): void
    {
        $edition = $this->approvedEdition();
        $link = $edition->links()->firstOrFail();

        $this->get(route('newsletter.click', [$edition->id, $link->id]))
            ->assertRedirect($link->url)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get(route('newsletter.click', [$edition->id, $link->id]));

        $this->assertSame(2, $link->fresh()->clicks);
    }

    public function test_head_requests_do_not_count(): void
    {
        $edition = $this->approvedEdition();
        $link = $edition->links()->firstOrFail();

        $this->call('HEAD', route('newsletter.click', [$edition->id, $link->id]));

        $this->assertSame(0, $link->fresh()->clicks);
    }

    public function test_unknown_mismatched_or_draft_links_are_404(): void
    {
        $edition = $this->approvedEdition();
        $other = $this->approvedEdition();
        $link = $edition->links()->firstOrFail();

        $this->get(route('newsletter.click', [$edition->id, 999999]))->assertNotFound();
        $this->get(route('newsletter.click', [$other->id, $link->id]))->assertNotFound();

        app(NewsletterEditionService::class)->unapprove($edition);
        $this->assertSame(0, NewsletterLink::where('edition_id', $edition->id)->count(), 'Volver a borrador elimina sus enlaces.');
        $this->get(route('newsletter.click', [$edition->id, $link->id]))->assertNotFound();
    }

    public function test_the_redirect_is_not_cached_or_open_to_arbitrary_urls(): void
    {
        $edition = $this->approvedEdition();
        $link = $edition->links()->firstOrFail();

        $this->get(route('newsletter.click', [$edition->id, $link->id]).'?to=https://evil.example')->assertRedirect($link->url);
        $this->assertStringContainsString('no-store', $this->get(route('newsletter.click', [$edition->id, $link->id]))->headers->get('Cache-Control'));
    }

    public function test_test_mails_of_a_draft_use_direct_links(): void
    {
        $artist = Interprete::create(['interprete' => 'Artista borrador '.uniqid(), 'slug' => 'bor-'.uniqid(), 'biografia' => '<p>Bio</p>', 'estado' => 1]);
        $artist->forceFill(['created_at' => now()->subWeek()])->save();
        [$start, $end] = NewsletterEditionService::lastWeek();
        $draft = NewsletterEdition::create([
            'edition_key' => 'borrador-'.uniqid(), 'subject' => 'Borrador', 'status' => 'draft',
            'period_start' => $start->toDateString(), 'period_end' => $end->toDateString(), 'selection' => ['artists' => [$artist->id]],
        ]);

        $this->actingAs($this->admin)->get(route('backend.newsletter.editions.preview', $draft))->assertOk()
            ->assertSee(route('artista.show', $artist->slug), false)->assertDontSee('/newsletter/c/', false);
    }

    public function test_the_administrator_sees_clicks_per_link_on_a_sent_edition(): void
    {
        $edition = $this->approvedEdition();
        $link = $edition->links()->firstOrFail();
        $edition->update(['status' => 'sent', 'sent_at' => now()]);
        $link->update(['clicks' => 7]);

        $this->actingAs($this->admin)->get(route('backend.newsletter.editions.edit', $edition))->assertOk()
            ->assertSee('data-newsletter-clicks', false)->assertSee($link->title)->assertSee('7 en total');
    }

    // ---------------- Rebotes ----------------

    public function test_hard_bounces_are_detected_and_soft_failures_are_not(): void
    {
        $hard = [
            'Expected response code "250/251/252" but got code "550", with message "550 5.1.1 The email account that you tried to reach does not exist."',
            'Expected response code "250" but got code "550", with message "550 User unknown".',
            'Expected response code "250" but got code "553", with message "553 5.1.3 Invalid recipient address".',
        ];
        $notHard = [
            'Expected response code "250" but got code "554", with message "554 5.7.1 Message rejected as spam".',
            'Expected response code "250" but got code "550", with message "550 5.7.1 blocked by policy, reputation too low".',
            'Expected response code "250" but got code "421", with message "421 Try again later".',
            'Connection could not be established with host "smtp.example.test": timeout',
            'Expected response code "250" but got code "452", with message "452 4.2.2 mailbox full".',
        ];

        foreach ($hard as $message) {
            $this->assertTrue(NewsletterBounceDetector::isHardBounce(new \RuntimeException($message)), $message);
        }

        foreach ($notHard as $message) {
            $this->assertFalse(NewsletterBounceDetector::isHardBounce(new \RuntimeException($message)), $message);
        }
    }

    public function test_a_hard_bounce_while_sending_marks_the_address_and_does_not_retry(): void
    {
        $edition = $this->approvedEdition();
        $edition->update(['status' => 'sent']);
        $sub = $this->subscriber('noexiste@example.test');

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Expected response code "250" but got code "550", with message "550 5.1.1 User unknown".'));

        SendNewsletterJob::dispatchSync($sub->id, $edition->id);

        $this->assertSame('bounced', $sub->fresh()->status);
        $this->assertSame(0, \App\Models\NewsletterDelivery::where('subscriber_id', $sub->id)->count());
    }

    public function test_a_policy_failure_while_sending_is_retried_and_does_not_touch_the_address(): void
    {
        $edition = $this->approvedEdition();
        $edition->update(['status' => 'sent']);
        $sub = $this->subscriber('bien@example.test');

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Expected response code "250" but got code "554", with message "554 5.7.1 rejected as spam".'));

        $this->expectException(\RuntimeException::class);

        try {
            SendNewsletterJob::dispatchSync($sub->id, $edition->id);
        } finally {
            $this->assertSame('active', $sub->fresh()->status);
            $this->assertSame(0, \App\Models\NewsletterDelivery::where('subscriber_id', $sub->id)->count());
        }
    }

    public function test_bounced_addresses_are_not_deliverable_and_the_administrator_can_mark_one(): void
    {
        $sub = $this->subscriber('manual@example.test');

        $this->actingAs($this->admin)->post(route('backend.newsletter.bounced', $sub))->assertSessionHas('success');

        $this->assertSame('bounced', $sub->fresh()->status);
        $this->assertFalse($sub->fresh()->isDeliverable());
        $this->actingAs($this->admin)->get(route('backend.newsletter.index', ['status' => 'bounced']))->assertOk()->assertSee('manual@example.test')->assertSee('Rebotado');
    }

    public function test_only_administrators_can_mark_bounces(): void
    {
        $sub = $this->subscriber('privado@example.test');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('backend.newsletter.bounced', $sub))->assertForbidden();
        $this->assertSame('active', $sub->fresh()->status);
    }
}
