<?php

namespace Tests\Feature;

use App\Mail\ContactRecieveEmail;
use App\Mail\ContactSendEmail;
use App\Models\ContactMessage;
use App\Models\User;
use App\Services\ContactFormGuard;
use App\Support\ResponseCache\PublicPagesCacheProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
    }

    private function user(?string $role = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        if ($role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    /** Un formulario "mostrado hace 10 segundos". */
    private function payload(array $extra = []): array
    {
        $token = app(ContactFormGuard::class)->token();
        $this->travel(10)->seconds();

        return array_merge([
            'name' => 'Ana Pérez', 'email' => 'ana@example.test', 'issue' => 'Consulta General',
            'message' => 'Hola, quiero consultar sobre un festival de la zona.', 'consent' => '1', 'form_token' => $token, 'website' => '',
        ], $extra);
    }

    // ---------------- Pagina y visibilidad ----------------

    public function test_the_page_renders_a_modern_accessible_form_without_the_static_captcha(): void
    {
        $html = $this->get(route('contacto'))->assertOk()
            ->assertSee('<title>Contacto | Mi Folklore Argentino</title>', false)
            ->assertSee('name="form_token"', false)
            ->assertSee('name="website"', false)
            ->assertSee('política de privacidad')
            ->getContent();

        foreach (['name', 'email', 'issue', 'message'] as $field) {
            $this->assertStringContainsString('for="'.$field.'"', $html, "falta el label de $field");
        }
        $this->assertStringNotContainsString('3 + 4', $html);
        $this->assertStringNotContainsString('captcha', strtolower($html));
        $this->assertStringNotContainsString('form-control', $html, 'sin clases de Bootstrap en el front');
        $this->assertStringContainsString('rel="canonical"', $html);
    }

    public function test_the_form_is_reachable_from_the_menu_and_the_footer(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(3, substr_count($html, 'href="'.route('contacto').'"'), 'menú Más, footer (lista) y botón del footer');
        $this->assertStringContainsString('Escribinos', $html);
    }

    public function test_the_contact_page_is_never_page_cached(): void
    {
        $profile = app(PublicPagesCacheProfile::class);

        $this->assertFalse($profile->shouldCacheRequest(Request::create('/contacto', 'GET')));
        $this->assertFalse($profile->shouldCacheRequest(Request::create('/contacto', 'POST')));
    }

    public function test_logged_in_users_get_their_name_and_email_prefilled(): void
    {
        $user = $this->user('colaborador', ['name' => 'Valeria Quiroga', 'email' => 'vale@example.test']);

        $this->actingAs($user)->get(route('contacto'))->assertOk()->assertSee('value="Valeria Quiroga"', false)->assertSee('value="vale@example.test"', false);
    }

    public function test_the_table_has_the_legacy_and_the_new_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('contact_messages', ['id', 'nombre', 'email', 'asunto', 'mensaje', 'fecha_envio', 'status', 'user_id', 'ip_hash', 'read_at', 'answered_at', 'spam_reason']));
    }

    // ---------------- Envio correcto ----------------

    public function test_a_valid_message_is_stored_and_both_emails_are_sent(): void
    {
        Mail::fake();
        $user = $this->user();

        $this->actingAs($user)->post(route('contacto.store'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('contacto'))
            ->assertSessionHas('contact_success');

        $message = ContactMessage::where('email', 'ana@example.test')->firstOrFail();
        $this->assertSame('Ana Pérez', $message->nombre);
        $this->assertSame('Consulta General', $message->asunto);
        $this->assertSame('new', $message->status);
        $this->assertSame($user->id, (int) $message->user_id);
        $this->assertNotNull($message->fecha_envio);
        $this->assertSame(64, strlen((string) $message->ip_hash));
        $this->assertStringNotContainsString('127.0.0.1', (string) $message->ip_hash);

        Mail::assertSent(ContactRecieveEmail::class, fn ($mail) => $mail->hasTo(config('contact.notify_to')) && $mail->hasReplyTo('ana@example.test') && str_contains($mail->envelope()->subject, 'Consulta General'));
        Mail::assertSent(ContactSendEmail::class, fn ($mail) => $mail->hasTo('ana@example.test'));
    }

    public function test_the_page_confirms_the_submission_on_screen(): void
    {
        Mail::fake();

        $this->post(route('contacto.store'), $this->payload());

        $this->get(route('contacto'))->assertOk()->assertSee('Gracias por escribirnos')->assertSee('role="status"', false);
    }

    public function test_the_notification_address_and_the_confirmation_are_configurable(): void
    {
        Mail::fake();
        config(['contact.notify_to' => 'equipo@example.test', 'contact.send_confirmation' => false]);

        $this->post(route('contacto.store'), $this->payload());

        Mail::assertSent(ContactRecieveEmail::class, fn ($mail) => $mail->hasTo('equipo@example.test'));
        Mail::assertNotSent(ContactSendEmail::class);
    }

    public function test_a_mail_failure_is_logged_and_the_message_is_not_lost(): void
    {
        Log::spy();
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('smtp caído'));

        $this->post(route('contacto.store'), $this->payload())->assertSessionHasNoErrors()->assertSessionHas('contact_success');

        $this->assertSame(1, ContactMessage::where('email', 'ana@example.test')->count());
        Log::shouldHaveReceived('error')->atLeast()->once();
    }

    // ---------------- Validacion ----------------

    public function test_validation_rules_and_messages(): void
    {
        // Son 7 envíos seguidos: el límite por IP (probado aparte) no es lo que se verifica acá.
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        Mail::fake();

        $this->post(route('contacto.store'), $this->payload(['name' => '']))->assertSessionHasErrors('name');
        $this->post(route('contacto.store'), $this->payload(['email' => 'no-es-un-email']))->assertSessionHasErrors('email');
        $this->post(route('contacto.store'), $this->payload(['issue' => 'Asunto inventado']))->assertSessionHasErrors('issue');
        $this->post(route('contacto.store'), $this->payload(['message' => 'corto']))->assertSessionHasErrors('message');
        $this->post(route('contacto.store'), $this->payload(['message' => str_repeat('a', 5001)]))->assertSessionHasErrors('message');
        $this->post(route('contacto.store'), $this->payload(['consent' => null]))->assertSessionHasErrors('consent');

        $this->assertSame(0, ContactMessage::count());
        Mail::assertNothingSent();
    }

    public function test_line_breaks_in_the_name_cannot_inject_mail_headers(): void
    {
        Mail::fake();

        $this->post(route('contacto.store'), $this->payload(['name' => "Ana\r\nBcc: victima@example.test"]))->assertSessionHasNoErrors();

        $stored = ContactMessage::firstOrFail();
        $this->assertStringNotContainsString("\n", $stored->nombre);
        $this->assertStringNotContainsString("\r", $stored->nombre);
        Mail::assertSent(ContactRecieveEmail::class, function ($mail) {
            $reply = $mail->envelope()->replyTo[0];

            return ! str_contains($reply->name, "\n") && ! str_contains($reply->name, "\r") && $mail->hasReplyTo('ana@example.test');
        });
    }

    // ---------------- Anti-spam ----------------

    public function test_honeypot_submissions_look_successful_but_store_and_send_nothing(): void
    {
        Mail::fake();

        $this->post(route('contacto.store'), $this->payload(['website' => 'http://spam.example']))->assertSessionHas('contact_success')->assertSessionHasNoErrors();

        $this->assertSame(0, ContactMessage::count());
        Mail::assertNothingSent();
    }

    public function test_too_fast_expired_or_tampered_tokens_are_rejected_with_a_clear_message(): void
    {
        Mail::fake();
        $guard = app(ContactFormGuard::class);

        // Demasiado rápido: token recién emitido.
        $fresh = array_merge($this->payload(), ['form_token' => $guard->token()]);
        $this->post(route('contacto.store'), $fresh)->assertSessionHasErrors(['form_token' => 'Enviaste el formulario demasiado rápido. Esperá unos segundos y volvé a intentar.']);

        // Vencido.
        $old = $this->payload();
        $this->travel(3)->hours();
        $this->post(route('contacto.store'), $old)->assertSessionHasErrors(['form_token' => 'El formulario venció. Revisá tu mensaje y volvé a enviarlo.']);

        // Adulterado o ausente.
        $this->post(route('contacto.store'), $this->payload(['form_token' => 'inventado']))->assertSessionHasErrors('form_token');
        $this->post(route('contacto.store'), $this->payload(['form_token' => null]))->assertSessionHasErrors('form_token');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_rejected_submissions_keep_what_the_person_typed(): void
    {
        $this->from(route('contacto'))->post(route('contacto.store'), $this->payload(['form_token' => 'inventado']))
            ->assertRedirect(route('contacto'))
            ->assertSessionHasInput('message', 'Hola, quiero consultar sobre un festival de la zona.');
    }

    public function test_messages_full_of_links_are_stored_as_spam_without_emails(): void
    {
        Mail::fake();
        $message = 'Mirá esto http://a.example http://b.example http://c.example y también http://d.example';

        $this->post(route('contacto.store'), $this->payload(['message' => $message]))->assertSessionHas('contact_success');

        $stored = ContactMessage::firstOrFail();
        $this->assertSame('spam', $stored->status);
        $this->assertSame('too_many_links', $stored->spam_reason);
        Mail::assertNothingSent();
    }

    public function test_the_same_message_twice_is_stored_once(): void
    {
        Mail::fake();
        $payload = $this->payload();

        $this->post(route('contacto.store'), $payload);
        $this->post(route('contacto.store'), $payload)->assertSessionHas('contact_success');

        $this->assertSame(1, ContactMessage::count());
        Mail::assertSent(ContactRecieveEmail::class, 1);
    }

    public function test_each_email_address_is_limited_per_hour(): void
    {
        Mail::fake();

        foreach (range(1, 3) as $i) {
            $this->post(route('contacto.store'), $this->payload(['message' => "Mensaje distinto número $i para probar el límite."]))->assertSessionHasNoErrors();
        }

        $this->post(route('contacto.store'), $this->payload(['message' => 'Un cuarto mensaje distinto, ya fuera del límite.']))->assertSessionHasErrors('email');

        $this->assertSame(3, ContactMessage::count());
    }

    public function test_submissions_are_throttled_per_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contacto.store'), []);
        }

        $this->post(route('contacto.store'), [])->assertStatus(429);
    }

    // ---------------- Backoffice ----------------

    private function seedMessages(): array
    {
        $mk = fn (array $a) => ContactMessage::create(array_merge(['nombre' => 'Persona', 'email' => 'p@example.test', 'asunto' => 'Consulta General', 'mensaje' => 'Mensaje de prueba', 'fecha_envio' => now(), 'status' => 'new'], $a));

        return [
            'nuevo' => $mk(['nombre' => 'Laura Nueva', 'mensaje' => 'Quiero saber de las peñas de Cosquín']),
            'leido' => $mk(['nombre' => 'Raúl Leído', 'status' => 'read', 'email' => 'raul@example.test']),
            'spam' => $mk(['nombre' => 'Bot Spam', 'status' => 'spam', 'spam_reason' => 'too_many_links']),
        ];
    }

    public function test_only_administrators_can_open_the_inbox(): void
    {
        $messages = $this->seedMessages();

        $this->get(route('backend.contact-messages.index'))->assertRedirect(route('login'));
        $this->actingAs($this->user('colaborador'))->get(route('backend.contact-messages.index'))->assertForbidden();
        $this->get(route('backend.contact-messages.show', $messages['nuevo']))->assertForbidden();
        $this->patch(route('backend.contact-messages.status', $messages['nuevo']), ['status' => 'spam'])->assertForbidden();
        $this->delete(route('backend.contact-messages.destroy', $messages['nuevo']))->assertForbidden();

        $this->assertSame('new', $messages['nuevo']->fresh()->status);
        $this->actingAs($this->user('administrador'))->get(route('backend.contact-messages.index'))->assertOk();
    }

    public function test_the_inbox_lists_filters_and_searches(): void
    {
        $this->seedMessages();
        $this->actingAs($this->user('administrador'));

        $this->get(route('backend.contact-messages.index'))->assertOk()->assertSee('Laura Nueva')->assertDontSee('Raúl Leído')->assertDontSee('Bot Spam');
        $this->get(route('backend.contact-messages.index', ['status' => 'spam']))->assertSee('Bot Spam')->assertDontSee('Laura Nueva');
        $this->get(route('backend.contact-messages.index', ['status' => 'all']))->assertSee('Laura Nueva')->assertSee('Raúl Leído')->assertSee('Bot Spam');
        $this->get(route('backend.contact-messages.index', ['status' => 'all', 'q' => 'Cosquín']))->assertSee('Laura Nueva')->assertDontSee('Raúl Leído');
        $this->get(route('backend.contact-messages.index', ['status' => 'all', 'q' => 'raul@example']))->assertSee('Raúl Leído')->assertDontSee('Laura Nueva');
    }

    public function test_opening_a_new_message_marks_it_as_read_and_offers_a_reply_link(): void
    {
        $message = $this->seedMessages()['nuevo'];

        $html = $this->actingAs($this->user('administrador'))->get(route('backend.contact-messages.show', $message))->assertOk()
            ->assertSee('Laura Nueva')
            ->assertSee('Responder por email')
            ->getContent();

        $this->assertStringContainsString('mailto:p@example.test?subject=Re%3A%20Consulta%20General', $html);
        $fresh = $message->fresh();
        $this->assertSame('read', $fresh->status);
        $this->assertNotNull($fresh->read_at);
    }

    public function test_the_message_text_is_escaped_in_the_backoffice(): void
    {
        $message = ContactMessage::create(['nombre' => '<b>Hack</b>', 'email' => 'h@example.test', 'asunto' => 'Otros', 'mensaje' => '<script>alert(1)</script>', 'fecha_envio' => now(), 'status' => 'new']);

        $html = $this->actingAs($this->user('administrador'))->get(route('backend.contact-messages.show', $message))->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_statuses_can_be_changed_and_messages_deleted(): void
    {
        $message = $this->seedMessages()['nuevo'];
        $this->actingAs($this->user('administrador'));

        $this->patch(route('backend.contact-messages.status', $message), ['status' => 'answered'])->assertSessionHasNoErrors();
        $fresh = $message->fresh();
        $this->assertSame('answered', $fresh->status);
        $this->assertNotNull($fresh->answered_at);

        $this->patch(route('backend.contact-messages.status', $message), ['status' => 'spam'])->assertSessionHasNoErrors();
        $this->assertSame('spam', $message->fresh()->status);
        $this->patch(route('backend.contact-messages.status', $message), ['status' => 'inventado'])->assertSessionHasErrors('status');

        $this->delete(route('backend.contact-messages.destroy', $message))->assertRedirect();
        $this->assertNull(ContactMessage::find($message->id));
    }

    public function test_the_dashboard_counts_new_messages_for_administrators_only(): void
    {
        $this->seedMessages();

        $this->actingAs($this->user('administrador'))->get(route('dashboard'))->assertOk()->assertSee('Mensaje de contacto nuevo')->assertSee('Mensajes de contacto');
    }

    public function test_collaborators_do_not_see_the_inbox_in_their_dashboard_or_sidebar(): void
    {
        $this->seedMessages();

        $this->actingAs($this->user('colaborador'))->get(route('dashboard'))->assertOk()
            ->assertDontSee('Mensaje de contacto nuevo')
            ->assertDontSee('admin/contact-messages');
    }
}
