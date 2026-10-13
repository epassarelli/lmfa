<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Mail\ConfirmNewsletterSubscriptionMail;
use App\Mail\WeeklyNewsletterMail;
use App\Models\Interprete;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NewsletterSafeBaseTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        // Los contadores de límite viven en caché: cada test empieza limpio.
        Cache::flush();
    }

    private function subscriber(string $email, string $status, bool $confirmed = false): NewsletterSubscriber
    {
        return NewsletterSubscriber::create([
            'email' => $email, 'status' => $status, 'source' => 'sidebar',
            'confirmed_at' => $confirmed ? now() : null,
            'unsubscribed_at' => $status === 'unsubscribed' ? now() : null,
        ]);
    }

    private function user(?string $role): User
    {
        $user = User::factory()->create();

        if ($role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    // ---------------- Alta y doble confirmación ----------------

    public function test_a_new_subscription_is_pending_until_confirmed_from_the_mailbox(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'Nueva.Persona@Example.test', 'source' => 'sidebar'])
            ->assertSessionHas('newsletter_success', \App\Http\Controllers\Frontend\NewsletterController::NEUTRAL_MESSAGE);

        $sub = NewsletterSubscriber::where('email', 'nueva.persona@example.test')->firstOrFail();
        $this->assertSame('pending', $sub->status);
        $this->assertNull($sub->confirmed_at);
        $this->assertNotNull($sub->consented_at);
        $this->assertNotNull($sub->consent_ip_hash);
        $this->assertFalse($sub->isDeliverable());
        Mail::assertSent(ConfirmNewsletterSubscriptionMail::class, fn ($mail) => $mail->hasTo('nueva.persona@example.test'));
    }

    public function test_the_confirmation_link_only_shows_a_button_and_the_post_confirms(): void
    {
        $sub = $this->subscriber('confirma@example.test', 'pending');
        $url = $sub->confirmationUrl();

        $this->get($url)->assertOk()->assertSee('Confirmar mi suscripción')->assertSee('noindex', false);
        $this->assertNull($sub->fresh()->confirmed_at, 'Un GET (p. ej. un filtro de correo) no debe confirmar.');

        $this->post($url)->assertOk()->assertSee('Tu suscripción está confirmada');
        $sub->refresh();
        $this->assertSame('active', $sub->status);
        $this->assertNotNull($sub->confirmed_at);
        $this->assertTrue($sub->isDeliverable());
    }

    public function test_a_tampered_or_expired_confirmation_link_is_rejected(): void
    {
        $sub = $this->subscriber('vencido@example.test', 'pending');

        $this->get(route('newsletter.confirm.show', $sub->token))->assertForbidden();

        $expired = \Illuminate\Support\Facades\URL::temporarySignedRoute('newsletter.confirm.show', now()->subMinute(), ['token' => $sub->token]);
        $this->get($expired)->assertForbidden();
        $this->post($expired)->assertForbidden();
        $this->assertNull($sub->fresh()->confirmed_at);
    }

    public function test_the_response_is_the_same_whether_or_not_the_email_exists(): void
    {
        $this->subscriber('activo@example.test', 'active', true);

        $a = $this->post(route('newsletter.subscribe'), ['email' => 'activo@example.test']);
        $b = $this->post(route('newsletter.subscribe'), ['email' => 'otro.nuevo@example.test']);

        $this->assertSame(session('newsletter_success'), \App\Http\Controllers\Frontend\NewsletterController::NEUTRAL_MESSAGE);
        $a->assertSessionHas('newsletter_success', \App\Http\Controllers\Frontend\NewsletterController::NEUTRAL_MESSAGE);
        $b->assertSessionHas('newsletter_success', \App\Http\Controllers\Frontend\NewsletterController::NEUTRAL_MESSAGE);
        Mail::assertSent(ConfirmNewsletterSubscriptionMail::class, 1); // solo al nuevo; al confirmado no se le escribe
    }

    public function test_someone_who_unsubscribed_is_not_reactivated_by_a_third_party(): void
    {
        $sub = $this->subscriber('baja@example.test', 'unsubscribed');

        $this->post(route('newsletter.subscribe'), ['email' => 'baja@example.test']);

        $this->assertSame('unsubscribed', $sub->fresh()->status);
        $this->assertNull($sub->fresh()->confirmed_at);
        $this->assertFalse($sub->fresh()->isDeliverable());

        // Solo si confirma desde su correo vuelve a estar activo.
        $this->post($sub->fresh()->confirmationUrl())->assertOk();
        $this->assertTrue($sub->fresh()->isDeliverable());
    }

    public function test_confirmation_emails_are_not_repeated_within_ten_minutes(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'repite@example.test']);
        $this->post(route('newsletter.subscribe'), ['email' => 'repite@example.test']);
        Mail::assertSent(ConfirmNewsletterSubscriptionMail::class, 1);

        $this->travel(11)->minutes();
        $this->post(route('newsletter.subscribe'), ['email' => 'repite@example.test']);
        Mail::assertSent(ConfirmNewsletterSubscriptionMail::class, 2);
    }

    public function test_bounced_addresses_are_never_written_to_again(): void
    {
        $this->subscriber('rebota@example.test', 'bounced');

        $this->post(route('newsletter.subscribe'), ['email' => 'rebota@example.test']);

        Mail::assertNothingSent();
    }

    public function test_the_honeypot_and_the_rate_limit_stop_abuse(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'bot@example.test', 'company_site' => 'http://spam.test'])
            ->assertSessionHas('newsletter_success');
        $this->assertSame(0, NewsletterSubscriber::where('email', 'bot@example.test')->count());
        Mail::assertNothingSent();

        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('newsletter.subscribe'), ['email' => "limite{$i}@example.test"])->assertSessionHasNoErrors();
        }

        $this->post(route('newsletter.subscribe'), ['email' => 'limite6@example.test'])->assertStatus(429);
        $this->assertSame(0, NewsletterSubscriber::where('email', 'limite6@example.test')->count());
    }

    public function test_an_invalid_email_is_rejected(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'no-es-un-mail'])->assertSessionHasErrors('email');
    }

    // ---------------- Baja ----------------

    public function test_unsubscribing_needs_a_post_and_the_get_only_shows_a_button(): void
    {
        $sub = $this->subscriber('sale@example.test', 'active', true);

        $this->get(route('newsletter.unsubscribe', $sub->token))->assertOk()->assertSee('Sí, darme de baja')->assertSee('noindex', false);
        $this->assertSame('active', $sub->fresh()->status);

        $this->post(route('newsletter.unsubscribe.perform', $sub->token))->assertOk()->assertSee('Te diste de baja correctamente');
        $this->assertSame('unsubscribed', $sub->fresh()->status);
        $this->assertNotNull($sub->fresh()->unsubscribed_at);
    }

    public function test_one_click_unsubscribe_posts_without_a_csrf_token(): void
    {
        $except = (new \ReflectionProperty(VerifyCsrfToken::class, 'except'))->getValue(app(VerifyCsrfToken::class));

        $this->assertContains('newsletter/unsubscribe/*', $except);
    }

    public function test_the_newsletter_mail_declares_one_click_unsubscribe(): void
    {
        $sub = $this->subscriber('headers@example.test', 'active', true);
        $edition = \App\Models\NewsletterEdition::create([
            'edition_key' => 'test-headers', 'subject' => 'Asunto', 'status' => 'approved',
            'period_start' => now()->subWeek()->toDateString(), 'period_end' => now()->toDateString(),
        ]);

        $headers = (new \App\Mail\NewsletterEditionMail($edition, $sub, []))->headers()->text;

        $this->assertSame('<'.route('newsletter.unsubscribe', $sub->token).'>', $headers['List-Unsubscribe']);
        $this->assertSame('List-Unsubscribe=One-Click', $headers['List-Unsubscribe-Post']);
    }

    // ---------------- Programación ----------------

    public function test_nothing_is_scheduled_that_sends_without_approval(): void
    {
        $commands = collect(app(Schedule::class)->events())->map(fn ($event) => (string) $event->command)->implode(' ');

        $this->assertStringNotContainsString('newsletter:send-weekly', $commands);
        $this->assertStringContainsString('newsletter:build-draft', $commands);
        $this->assertStringContainsString('newsletter:dispatch-scheduled', $commands);
    }
    // ---------------- Backoffice ----------------

    public function test_only_administrators_can_see_or_manage_subscribers(): void
    {
        $sub = $this->subscriber('privado@example.test', 'active', true);

        foreach ([null, 'colaborador'] as $role) {
            $user = $this->user($role);
            $this->actingAs($user)->get(route('backend.newsletter.index'))->assertForbidden();
            $this->actingAs($user)->post(route('backend.newsletter.toggle', $sub))->assertForbidden();
            $this->actingAs($user)->post(route('backend.newsletter.confirmation', $sub))->assertForbidden();
            $this->actingAs($user)->delete(route('backend.newsletter.destroy', $sub))->assertForbidden();
        }

        $this->assertNotNull(NewsletterSubscriber::find($sub->id));
        $this->actingAs($this->user('administrador'))->get(route('backend.newsletter.index'))->assertOk()->assertSee('privado@example.test')->assertSee('data-newsletter-help', false);
    }

    public function test_an_administrator_can_request_confirmation_deactivate_and_delete_but_not_reactivate(): void
    {
        $admin = $this->user('administrador');
        $legacy = $this->subscriber('heredado2@example.test', 'active', false);
        $gone = $this->subscriber('salio@example.test', 'unsubscribed');
        $spam = $this->subscriber('spam@example.test', 'pending');

        $this->actingAs($admin)->get(route('backend.newsletter.index', ['status' => 'unconfirmed']))->assertOk()
            ->assertSee('heredado2@example.test')->assertSee('spam@example.test')->assertDontSee('salio@example.test');

        $this->actingAs($admin)->post(route('backend.newsletter.confirmation', $legacy))->assertSessionHas('success');
        Mail::assertSent(ConfirmNewsletterSubscriptionMail::class, fn ($mail) => $mail->hasTo('heredado2@example.test'));
        $this->assertFalse($legacy->fresh()->isDeliverable(), 'Pedir confirmación no activa: la persona tiene que confirmar.');

        $this->actingAs($admin)->post(route('backend.newsletter.toggle', $gone));
        $this->assertSame('unsubscribed', $gone->fresh()->status, 'El administrador no reactiva a quien se dio de baja.');

        $this->actingAs($admin)->delete(route('backend.newsletter.destroy', $spam))->assertSessionHas('success');
        $this->assertNull(NewsletterSubscriber::find($spam->id));
    }
}
