<?php

namespace Tests\Feature;

use App\Jobs\SendNewsletterJob;
use App\Mail\NewsletterEditionMail;
use App\Models\Interprete;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterEdition;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Services\NewsletterEditionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NewsletterEditionTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = $this->user('administrador');
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

    private function artist(string $name, ?Carbon $createdAt = null, int $estado = 1): Interprete
    {
        $artist = Interprete::create(['interprete' => $name, 'slug' => 'nl-'.uniqid(), 'biografia' => '<p>Bio</p>', 'estado' => $estado]);
        $artist->forceFill(['created_at' => $createdAt ?? now()->subWeek()])->save();

        return $artist;
    }

    private function subscriber(string $email, string $status, bool $confirmed): NewsletterSubscriber
    {
        return NewsletterSubscriber::create(['email' => $email, 'status' => $status, 'source' => 'sidebar', 'confirmed_at' => $confirmed ? now() : null]);
    }

    private function edition(array $attributes = []): NewsletterEdition
    {
        [$start, $end] = NewsletterEditionService::lastWeek();

        return NewsletterEdition::create(array_merge([
            'edition_key' => 'weekly-test-'.uniqid(), 'subject' => 'Asunto de prueba', 'intro' => 'Intro', 'status' => 'draft',
            'period_start' => $start->toDateString(), 'period_end' => $end->toDateString(), 'selection' => [],
        ], $attributes));
    }

    // ---------------- Borrador ----------------

    public function test_the_draft_command_creates_a_draft_and_sends_nothing(): void
    {
        $artist = $this->artist('Artista del borrador '.uniqid());
        $this->subscriber('ok@example.test', 'active', true);

        $this->artisan('newsletter:build-draft')->assertSuccessful();

        $edition = NewsletterEdition::where('edition_key', NewsletterEditionService::keyFor(NewsletterEditionService::lastWeek()[0]))->firstOrFail();
        $this->assertSame('draft', $edition->status);
        $this->assertContains($artist->id, $edition->selection['artists']);
        Mail::assertNothingSent();
        $this->assertSame(0, NewsletterDelivery::count());

        $this->artisan('newsletter:build-draft')->assertSuccessful();
        $this->assertSame(1, NewsletterEdition::where('edition_key', $edition->edition_key)->count(), 'No duplica el borrador del período.');
    }

    public function test_the_draft_is_not_created_without_content(): void
    {
        $this->assertNull(app(NewsletterEditionService::class)->buildDraft(...NewsletterEditionService::lastWeek()));
        $this->assertSame(0, NewsletterEdition::count());
    }

    public function test_candidates_only_include_public_content_of_the_period(): void
    {
        $in = $this->artist('Dentro del período '.uniqid());
        $hidden = $this->artist('Inactivo '.uniqid(), null, 0);
        $old = $this->artist('Muy viejo '.uniqid(), now()->subMonths(2));

        $ids = array_column(app(NewsletterEditionService::class)->candidates(...NewsletterEditionService::lastWeek())['artists'], 'id');

        $this->assertContains($in->id, $ids);
        $this->assertNotContains($hidden->id, $ids);
        $this->assertNotContains($old->id, $ids);
    }

    // ---------------- Edición, vista previa y prueba ----------------

    public function test_the_administrator_edits_previews_and_sends_a_test_without_recording_deliveries(): void
    {
        $artist = $this->artist('Artista visible '.uniqid());
        $edition = $this->edition();

        $this->actingAs($this->admin)->get(route('backend.newsletter.editions.edit', $edition))->assertOk()->assertSee($artist->interprete);

        $this->actingAs($this->admin)->put(route('backend.newsletter.editions.update', $edition), [
            'subject' => 'Nuevo asunto', 'intro' => 'Nueva intro', 'selection' => ['artists' => [$artist->id]],
        ])->assertSessionHasNoErrors();

        $edition->refresh();
        $this->assertSame('Nuevo asunto', $edition->subject);
        $this->assertSame([$artist->id], $edition->selection['artists']);

        $this->actingAs($this->admin)->get(route('backend.newsletter.editions.preview', $edition))->assertOk()
            ->assertSee('Nueva intro')->assertSee($artist->interprete)->assertSee('Darme de baja');

        $this->actingAs($this->admin)->post(route('backend.newsletter.editions.test', $edition), ['email' => 'prueba@example.test'])->assertSessionHas('success');
        Mail::assertSent(NewsletterEditionMail::class, fn ($mail) => $mail->hasTo('prueba@example.test') && str_starts_with($mail->envelope()->subject, '[PRUEBA]'));
        $this->assertSame(0, NewsletterDelivery::count());
        $this->assertSame('draft', $edition->fresh()->status);
    }

    // ---------------- Aprobación ----------------

    public function test_approving_requires_subject_and_content_and_freezes_what_was_reviewed(): void
    {
        $artist = $this->artist('Artista congelado '.uniqid());
        $edition = $this->edition(['selection' => []]);

        $this->actingAs($this->admin)->post(route('backend.newsletter.editions.approve', $edition))->assertSessionHasErrors('selection');
        $this->assertSame('draft', $edition->fresh()->status);

        $edition->update(['selection' => ['artists' => [$artist->id]]]);
        $this->actingAs($this->admin)->post(route('backend.newsletter.editions.approve', $edition))->assertSessionHasNoErrors();

        $edition->refresh();
        $this->assertSame('approved', $edition->status);
        $this->assertSame($this->admin->id, (int) $edition->approved_by);
        $this->assertSame($artist->interprete, $edition->snapshot['artists'][0]['title']);

        // El contenido original cambia después: lo aprobado queda igual.
        $artist->update(['interprete' => 'Nombre cambiado']);
        $this->assertStringNotContainsString('Nombre cambiado', json_encode($edition->fresh()->snapshot));

        // Aprobada ya no se edita, pero se puede volver a borrador.
        $this->actingAs($this->admin)->put(route('backend.newsletter.editions.update', $edition), ['subject' => 'x', 'selection' => []])->assertSessionHasErrors('status');
        $this->actingAs($this->admin)->post(route('backend.newsletter.editions.unapprove', $edition))->assertSessionHasNoErrors();
        $this->assertSame('draft', $edition->fresh()->status);
        $this->assertNull($edition->fresh()->snapshot);
    }

    // ---------------- Envío ----------------

    private function approvedEdition(?Carbon $scheduledAt = null): NewsletterEdition
    {
        $artist = $this->artist('Artista de envío '.uniqid());
        $edition = $this->edition(['selection' => ['artists' => [$artist->id]]]);

        return app(NewsletterEditionService::class)->approve($edition, $this->admin, $scheduledAt);
    }

    public function test_only_an_approved_edition_is_sent_only_to_confirmed_subscribers_and_only_once(): void
    {
        $this->subscriber('ok@example.test', 'active', true);
        $this->subscriber('pendiente@example.test', 'pending', false);
        $this->subscriber('heredado@example.test', 'active', false);
        $this->subscriber('baja@example.test', 'unsubscribed', true);
        $edition = $this->approvedEdition();

        $this->actingAs($this->admin)->post(route('backend.newsletter.editions.send', $edition))->assertSessionHas('success');

        Mail::assertSent(NewsletterEditionMail::class, 1);
        Mail::assertSent(NewsletterEditionMail::class, fn ($mail) => $mail->hasTo('ok@example.test') && $mail->edition->subject === 'Asunto de prueba');
        $edition->refresh();
        $this->assertSame('sent', $edition->status);
        $this->assertSame(1, $edition->recipients_count);
        $this->assertSame(1, NewsletterDelivery::where('edition_key', $edition->edition_key)->whereNotNull('sent_at')->count());

        // No se puede volver a enviar, ni editar, ni eliminar.
        $this->actingAs($this->admin)->post(route('backend.newsletter.editions.send', $edition))->assertSessionHasErrors('status');
        $this->actingAs($this->admin)->delete(route('backend.newsletter.editions.destroy', $edition))->assertSessionHas('error');
        Mail::assertSent(NewsletterEditionMail::class, 1);

        // Un reintento del job tampoco duplica.
        SendNewsletterJob::dispatchSync(NewsletterSubscriber::where('email', 'ok@example.test')->value('id'), $edition->id);
        Mail::assertSent(NewsletterEditionMail::class, 1);
    }

    public function test_a_draft_is_never_sent(): void
    {
        $this->subscriber('ok@example.test', 'active', true);
        $draft = $this->edition();

        $this->actingAs($this->admin)->post(route('backend.newsletter.editions.send', $draft))->assertSessionHasErrors('status');
        SendNewsletterJob::dispatchSync(NewsletterSubscriber::first()->id, $draft->id);

        Mail::assertNothingSent();
        $this->assertSame('draft', $draft->fresh()->status);
    }

    public function test_scheduled_editions_go_out_only_when_approved_and_due(): void
    {
        $this->subscriber('ok@example.test', 'active', true);
        $future = $this->approvedEdition(now()->addDay());
        $due = $this->approvedEdition(now()->subMinute());
        $unscheduled = $this->approvedEdition(null);
        $draftDue = $this->edition(['scheduled_at' => now()->subHour()]);

        $this->artisan('newsletter:dispatch-scheduled')->assertSuccessful();

        $this->assertSame('sent', $due->fresh()->status);
        $this->assertSame('approved', $future->fresh()->status);
        $this->assertSame('approved', $unscheduled->fresh()->status);
        $this->assertSame('draft', $draftDue->fresh()->status);
        Mail::assertSent(NewsletterEditionMail::class, 1);

        $this->artisan('newsletter:dispatch-scheduled')->assertSuccessful();
        Mail::assertSent(NewsletterEditionMail::class, 1);
    }

    public function test_a_subscriber_who_unsubscribes_before_the_job_runs_gets_nothing(): void
    {
        $sub = $this->subscriber('sale@example.test', 'active', true);
        $edition = $this->approvedEdition();
        $edition->update(['status' => 'sent']);

        $sub->update(['status' => 'unsubscribed']);
        SendNewsletterJob::dispatchSync($sub->id, $edition->id);

        Mail::assertNothingSent();
    }

    // ---------------- Ritmo de envío ----------------

    public function test_sending_is_paced_and_the_excess_goes_back_to_the_queue(): void
    {
        config(['queue.default' => 'database', 'newsletter.max_per_minute' => 2]);
        \Illuminate\Support\Facades\RateLimiter::clear('newsletter-send');

        $edition = $this->approvedEdition();
        $edition->update(['status' => 'sent']);
        $subs = collect(range(1, 3))->map(fn ($i) => $this->subscriber("ritmo{$i}@example.test", 'active', true));

        $released = 0;
        foreach ($subs as $sub) {
            $queueJob = \Mockery::mock(\Illuminate\Contracts\Queue\Job::class);
            $queueJob->shouldReceive('release')->with(30)->andReturnUsing(function () use (&$released) {
                $released++;
            });

            $job = new SendNewsletterJob($sub->id, $edition->id);
            $job->setJob($queueJob);
            $job->handle();
        }

        Mail::assertSent(NewsletterEditionMail::class, 2);
        $this->assertSame(1, $released, 'El tercero excede el tope y vuelve a la cola, sin enviarse ni perderse.');
        $this->assertSame(0, NewsletterDelivery::where('subscriber_id', $subs[2]->id)->count());

        \Illuminate\Support\Facades\RateLimiter::clear('newsletter-send');
    }

    public function test_rate_limit_retries_do_not_burn_the_job_attempts(): void
    {
        $job = new SendNewsletterJob(1, 1);

        $this->assertSame(0, $job->tries);
        $this->assertSame(3, $job->maxExceptions);
        $this->assertGreaterThan(now()->addHours(6), $job->retryUntil());
    }
    // ---------------- Permisos ----------------

    public function test_only_administrators_can_use_the_editions(): void
    {
        $edition = $this->edition();

        foreach ([null, 'colaborador'] as $role) {
            $user = $this->user($role);
            $this->actingAs($user)->get(route('backend.newsletter.editions.index'))->assertForbidden();
            $this->actingAs($user)->post(route('backend.newsletter.editions.store'))->assertForbidden();
            $this->actingAs($user)->get(route('backend.newsletter.editions.edit', $edition))->assertForbidden();
            $this->actingAs($user)->get(route('backend.newsletter.editions.preview', $edition))->assertForbidden();
            $this->actingAs($user)->post(route('backend.newsletter.editions.send', $edition))->assertForbidden();
        }

        $this->actingAs($this->admin)->get(route('backend.newsletter.editions.index'))->assertOk()->assertSee('data-newsletter-editions-help', false);
    }

    public function test_the_administrator_can_create_a_draft_for_last_week_from_the_backoffice(): void
    {
        $artist = $this->artist('Artista manual '.uniqid());

        $this->actingAs($this->admin)->post(route('backend.newsletter.editions.store'))->assertRedirect();

        $edition = NewsletterEdition::firstOrFail();
        $this->assertContains($artist->id, $edition->selection['artists']);
        $this->assertSame($this->admin->id, (int) $edition->created_by);
    }
}
