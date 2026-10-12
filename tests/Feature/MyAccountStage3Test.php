<?php

namespace Tests\Feature;

use App\Mail\EntityClaimResolvedMail;
use App\Models\Album;
use App\Models\ContactMessage;
use App\Models\EntityClaimRequest;
use App\Models\Interprete;
use App\Models\NewsletterSubscriber;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\DeviceLabel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MyAccountStage3Test extends TestCase
{
    use DatabaseTransactions;

    private function user(?string $role = 'colaborador', array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        if ($role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    private function artist(): Interprete
    {
        return Interprete::create(['interprete' => 'Artista '.uniqid(), 'slug' => 'a-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => $this->baseUserId()]);
    }

    private function organization(Interprete $artist): Organization
    {
        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => 'active']);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'is_primary' => true]);

        return $org;
    }

    private function claim(User $requester, Interprete $artist, array $extra = []): EntityClaimRequest
    {
        return EntityClaimRequest::create(array_merge([
            'user_id' => $requester->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id,
            'relationship' => 'press', 'message' => 'Soy prensa del artista y puedo acreditarlo.', 'status' => 'pending',
        ], $extra));
    }

    // ============ 1. Avisos por email de las solicitudes ============

    public function test_the_requester_is_emailed_when_an_admin_approves(): void
    {
        Mail::fake();
        $requester = $this->user(null, ['name' => 'Persona Solicitante']);
        $artist = $this->artist();
        $this->organization($artist);
        $claim = $this->claim($requester, $artist);

        $this->actingAs($this->user('administrador'))->post(route('backend.claims.approve', $claim), ['role' => 'editor', 'review_notes' => 'Te conocemos del festival.'])->assertSessionHasNoErrors();

        Mail::assertSent(EntityClaimResolvedMail::class, 1);
        Mail::assertSent(EntityClaimResolvedMail::class, function (EntityClaimResolvedMail $mail) use ($requester, $artist) {
            $mail->assertSeeInHtml('Aprobamos tu solicitud para administrar');
            $mail->assertSeeInHtml($artist->interprete);
            $mail->assertSeeInHtml('Editor');
            $mail->assertSeeInHtml('Te conocemos del festival.');
            $mail->assertSeeInHtml(route('backend.my-entities.index'));

            return $mail->hasTo($requester->email) && str_starts_with($mail->envelope()->subject, 'Aprobamos tu solicitud sobre');
        });
    }

    public function test_the_requester_is_emailed_the_reason_when_rejected_and_it_is_escaped(): void
    {
        Mail::fake();
        $requester = $this->user(null);
        $claim = $this->claim($requester, $this->artist());

        $this->actingAs($this->user('administrador'))->post(route('backend.claims.reject', $claim), ['review_notes' => 'No pudimos verificarlo <script>x</script>'])->assertSessionHasNoErrors();

        Mail::assertSent(EntityClaimResolvedMail::class, function (EntityClaimResolvedMail $mail) use ($requester) {
            $mail->assertSeeInHtml('no pudimos aprobarla');
            $mail->assertSeeInHtml('No pudimos verificarlo &lt;script&gt;x&lt;/script&gt;', false);
            $mail->assertDontSeeInHtml('<script>x</script>', false);

            return $mail->hasTo($requester->email) && str_contains($mail->envelope()->subject, 'no fue aprobada');
        });
    }

    public function test_owner_decisions_also_notify_the_requester(): void
    {
        Mail::fake();
        $owner = $this->user(null);
        $artist = $this->artist();
        $org = $this->organization($artist);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $owner->id, 'role' => 'owner', 'status' => 'active']);
        $first = $this->user(null);
        $second = $this->user(null);
        $approved = $this->claim($first, $artist);
        $rejected = $this->claim($second, $artist);

        $this->actingAs($owner)->post(route('backend.my-entities.review.approve', $approved), ['role' => 'editor'])->assertSessionHasNoErrors();
        $this->post(route('backend.my-entities.review.reject', $rejected), ['review_notes' => 'No lo conocemos.'])->assertSessionHasNoErrors();

        Mail::assertSent(EntityClaimResolvedMail::class, 2);
        Mail::assertSent(EntityClaimResolvedMail::class, fn ($m) => $m->hasTo($first->email) && str_contains($m->envelope()->subject, 'Aprobamos'));
        Mail::assertSent(EntityClaimResolvedMail::class, fn ($m) => $m->hasTo($second->email) && str_contains($m->envelope()->subject, 'no fue aprobada'));
    }

    public function test_a_creation_request_sends_exactly_one_email_when_approved(): void
    {
        Mail::fake();
        $requester = $this->user(null);
        $claim = EntityClaimRequest::create([
            'user_id' => $requester->id, 'kind' => 'create', 'entity_type' => (new Interprete)->getMorphClass(), 'entity_id' => null,
            'proposed_name' => 'Grupo Nuevo '.uniqid(), 'relationship' => 'artist', 'message' => 'Somos el grupo y lo podemos acreditar.', 'status' => 'pending',
        ]);

        $this->actingAs($this->user('administrador'))->post(route('backend.claims.approve', $claim), ['role' => 'owner'])->assertSessionHasNoErrors();

        Mail::assertSent(EntityClaimResolvedMail::class, 1);
        Mail::assertSent(EntityClaimResolvedMail::class, function (EntityClaimResolvedMail $mail) {
            $mail->assertSeeInHtml('Aprobamos tu pedido de alta');

            return true;
        });
    }

    public function test_a_mail_failure_never_undoes_the_resolution(): void
    {
        Log::spy();
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('smtp caído'));
        $claim = $this->claim($this->user(null), $this->artist());
        $this->organization($claim->entity);

        $this->actingAs($this->user('administrador'))->post(route('backend.claims.approve', $claim), ['role' => 'editor'])->assertSessionHasNoErrors();

        $this->assertSame('approved', $claim->fresh()->status);
        Log::shouldHaveReceived('error')->atLeast()->once();
    }

    public function test_anonymized_accounts_are_never_emailed(): void
    {
        Mail::fake();
        $ghost = $this->user(null, ['email' => 'deleted-user-9-abc@mifolkloreargentino.invalid']);
        $claim = $this->claim($ghost, $this->artist());

        $this->actingAs($this->user('administrador'))->post(route('backend.claims.reject', $claim), ['review_notes' => 'Sin respuesta.'])->assertSessionHasNoErrors();

        $this->assertSame('rejected', $claim->fresh()->status);
        Mail::assertNothingSent();
    }

    // ============ 2. Sesiones ============

    private function seedSession(User $user, string $id, string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36', ?int $lastActivity = null): void
    {
        DB::table('sessions')->insert(['id' => $id, 'user_id' => $user->id, 'ip_address' => '203.0.113.7', 'user_agent' => $userAgent, 'payload' => '', 'last_activity' => $lastActivity ?? time()]);
    }

    public function test_device_labels_are_readable(): void
    {
        $this->assertSame('Chrome en Windows', DeviceLabel::from('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML) Chrome/120.0 Safari/537.36'));
        $this->assertSame('Edge en Windows', DeviceLabel::from('Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 Chrome/120.0 Safari/537.36 Edg/120.0'));
        $this->assertSame('Firefox en Linux', DeviceLabel::from('Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0'));
        $this->assertSame('Chrome en Android', DeviceLabel::from('Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 Chrome/120.0 Mobile Safari/537.36'));
        $this->assertSame('Safari en iOS', DeviceLabel::from('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1'));
        $this->assertSame('Safari en macOS', DeviceLabel::from('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Version/17.0 Safari/605.1.15'));
        $this->assertSame('Navegador en un dispositivo desconocido', DeviceLabel::from(null));
    }

    public function test_the_page_lists_my_sessions_and_the_service_marks_the_current_one(): void
    {
        config(['session.driver' => 'database']);
        $user = $this->user();
        $other = $this->user();
        $this->seedSession($user, 'este-navegador');
        $this->seedSession($user, 'otro-dispositivo', 'Mozilla/5.0 (Linux; Android 14) Chrome/120.0 Mobile Safari/537.36', time() - 3600);
        $this->seedSession($other, 'sesion-de-otra-persona', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15');

        $this->actingAs($user)->get(route('backend.account.edit'))->assertOk()
            ->assertSee('Sesiones activas')
            ->assertSee('Chrome en Windows')
            ->assertSee('Chrome en Android')
            ->assertDontSee('Safari en macOS');

        // (la petición de arriba también dejó su propia sesión: se filtra por los ids sembrados)
        $rows = collect(app(\App\Services\UserSessionService::class)->list($user, 'este-navegador'));
        $this->assertSame(['este-navegador'], $rows->where('current', true)->pluck('id')->all());
        $this->assertTrue($rows->pluck('id')->contains('otro-dispositivo'));
        $this->assertFalse($rows->pluck('id')->contains('sesion-de-otra-persona'), 'nunca se listan sesiones de otra persona');
        $this->assertSame('otro-dispositivo', $rows->last()['id'], 'ordenadas por actividad reciente');
    }
    public function test_the_sessions_card_is_hidden_without_the_database_driver(): void
    {
        config(['session.driver' => 'file']);

        $this->actingAs($this->user())->get(route('backend.account.edit'))->assertOk()->assertDontSee('Sesiones activas');
    }

    public function test_closing_other_sessions_needs_the_password_and_keeps_the_current_one(): void
    {
        config(['session.driver' => 'database']);
        $user = $this->user();
        $other = $this->user();
        $this->seedSession($user, 'celular');
        $this->seedSession($user, 'notebook');
        $this->seedSession($other, 'sesion-de-otra-persona');
        $token = $user->remember_token;

        $this->actingAs($user)->post(route('backend.account.sessions.logout-others'), ['current_password' => 'incorrecta'])->assertSessionHasErrorsIn('sessions', 'current_password');
        $this->assertSame(2, DB::table('sessions')->whereIn('id', ['celular', 'notebook'])->count());
        $this->assertSame($token, $user->fresh()->remember_token);

        $this->post(route('backend.account.sessions.logout-others'), ['current_password' => 'password'])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame(0, DB::table('sessions')->whereIn('id', ['celular', 'notebook'])->count());
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', session()->getId())->count(), 'solo puede quedar la sesión de esta petición');
        $this->assertSame(1, DB::table('sessions')->where('user_id', $other->id)->count());
        $this->assertNotSame($token, $user->fresh()->remember_token);
    }

    public function test_changing_the_password_closes_the_other_sessions(): void
    {
        config(['session.driver' => 'database']);
        $user = $this->user();
        $this->seedSession($user, 'celular');

        $this->actingAs($user)->put(route('backend.account.password'), ['current_password' => 'password', 'password' => 'nuevaclave1', 'password_confirmation' => 'nuevaclave1'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Tu contraseña se actualizó y cerramos 1 sesión(es) en otros dispositivos.');

        $this->assertSame(0, DB::table('sessions')->where('id', 'celular')->count());
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', session()->getId())->count());
    }
    // ============ 3. Eliminar mi cuenta ============

    private function loadedAccount(): array
    {
        $user = $this->user('colaborador', ['name' => 'Nombre Real', 'email' => 'real@example.test', 'phone' => '351 5550000']);
        $artist = $this->artist();
        $org = $this->organization($artist);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => 'editor', 'status' => 'active']);
        $pending = $this->claim($user, $this->artist());
        $approved = $this->claim($user, $this->artist(), ['status' => 'approved']);
        NewsletterSubscriber::create(['email' => 'real@example.test', 'status' => 'active', 'source' => 'footer', 'user_id' => $user->id]);
        ContactMessage::create(['nombre' => 'Nombre Real', 'email' => 'real@example.test', 'asunto' => 'Otros', 'mensaje' => 'Mensaje de la cuenta', 'fecha_envio' => now(), 'status' => 'new', 'user_id' => $user->id]);
        UserNotification::create(['user_id' => $user->id, 'type' => 'info', 'title' => 'Aviso', 'body' => 'Texto', 'is_read' => false, 'created_at' => now()]);
        $user->socialAccounts()->create(['provider' => 'facebook', 'account_name' => 'Nombre Real', 'account_external_id' => 'fb-123', 'token_encrypted' => 'secreto', 'status' => 'active']);
        $user->createToken('api');
        $user->forceFill(['google_id' => 'g-1', 'facebook_id' => 'fb-123'])->save();
        $album = Album::create(['album' => 'Disco del usuario', 'slug' => 'disco-'.uniqid(), 'anio' => '2024', 'interprete_id' => $artist->id, 'user_id' => $user->id, 'estado' => 1, 'visitas' => 0]);
        $this->seedSession($user, 'sesion-vieja');

        return compact('user', 'artist', 'pending', 'approved', 'album');
    }

    public function test_erasing_the_account_anonymizes_it_and_removes_personal_links(): void
    {
        config(['session.driver' => 'database']);
        ['user' => $user, 'pending' => $pending, 'approved' => $approved, 'album' => $album] = $this->loadedAccount();

        $this->actingAs($user)->delete(route('backend.account.destroy'), ['current_password' => 'password', 'confirmation' => 'ELIMINAR'])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/')
            ->assertSessionHas('status');

        $this->assertGuest();
        $fresh = $user->fresh();
        $this->assertSame('Usuario eliminado '.$user->id, $fresh->name);
        $this->assertStringEndsWith('@mifolkloreargentino.invalid', $fresh->email);
        $this->assertNotSame('real@example.test', $fresh->email);
        $this->assertNull($fresh->phone);
        $this->assertNull($fresh->google_id);
        $this->assertNull($fresh->facebook_id);
        $this->assertNull($fresh->email_verified_at);
        $this->assertSame('deleted', $fresh->status);
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('password', $fresh->password));
        $this->assertSame([], $fresh->getRoleNames()->all());

        $this->assertSame(0, OrganizationMember::where('user_id', $user->id)->count());
        $this->assertSame('cancelled', $pending->fresh()->status);
        $this->assertSame('approved', $approved->fresh()->status);
        $this->assertSame(0, NewsletterSubscriber::where('email', 'real@example.test')->count());
        $this->assertSame(0, ContactMessage::where('email', 'real@example.test')->count());
        $this->assertSame(0, UserNotification::where('user_id', $user->id)->count());
        $this->assertSame(0, $user->socialAccounts()->count());
        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());

        // El contenido que cargó se conserva, ahora sin su nombre.
        $this->assertNotNull(Album::find($album->id));
        $this->assertSame($user->id, (int) $album->fresh()->user_id);
    }

    public function test_the_old_credentials_stop_working_after_erasing(): void
    {
        $user = $this->user('colaborador', ['email' => 'real2@example.test']);

        $this->actingAs($user)->delete(route('backend.account.destroy'), ['current_password' => 'password', 'confirmation' => 'ELIMINAR']);

        $this->post('/login', ['email' => 'real2@example.test', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_erasing_needs_the_password_and_the_exact_confirmation(): void
    {
        $user = $this->user('colaborador', ['name' => 'No Me Borres']);
        $this->actingAs($user);

        $this->delete(route('backend.account.destroy'), ['current_password' => 'incorrecta', 'confirmation' => 'ELIMINAR'])->assertSessionHasErrorsIn('delete', 'current_password');
        $this->delete(route('backend.account.destroy'), ['current_password' => 'password', 'confirmation' => 'eliminar'])->assertSessionHasErrorsIn('delete', 'confirmation');
        $this->delete(route('backend.account.destroy'), ['current_password' => 'password'])->assertSessionHasErrorsIn('delete', 'confirmation');

        $this->assertSame('No Me Borres', $user->fresh()->name);
        $this->assertAuthenticatedAs($user);
    }

    public function test_administrators_cannot_erase_themselves(): void
    {
        $admin = $this->user('administrador', ['name' => 'Admin Único']);

        $this->actingAs($admin)->delete(route('backend.account.destroy'), ['current_password' => 'password', 'confirmation' => 'ELIMINAR'])->assertForbidden();

        $this->assertSame('Admin Único', $admin->fresh()->name);
        $this->assertTrue($admin->fresh()->hasRole('administrador'));
        $this->get(route('backend.account.edit'))->assertOk()->assertSee('no se pueden eliminar desde acá');
    }

    public function test_the_page_warns_about_fichas_left_without_an_owner_and_explains_what_is_kept(): void
    {
        $owner = $this->user();
        $artist = $this->artist();
        $org = $this->organization($artist);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $owner->id, 'role' => 'owner', 'status' => 'active']);

        $this->actingAs($owner)->get(route('backend.account.edit'))->assertOk()
            ->assertSee('Eliminar mi cuenta')
            ->assertSee('Sos el único responsable de 1 ficha(s)')
            ->assertSee('el contenido que cargaste')
            ->assertSee('ELIMINAR');
    }

    public function test_a_second_owner_means_no_warning(): void
    {
        $owner = $this->user();
        $coOwner = $this->user();
        $org = $this->organization($this->artist());
        foreach ([$owner, $coOwner] as $u) {
            OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $u->id, 'role' => 'owner', 'status' => 'active']);
        }

        $this->actingAs($owner)->get(route('backend.account.edit'))->assertOk()->assertDontSee('Sos el único responsable');
    }

    public function test_erasing_is_throttled(): void
    {
        $this->actingAs($this->user());

        for ($i = 0; $i < 3; $i++) {
            $this->delete(route('backend.account.destroy'), ['current_password' => 'mala', 'confirmation' => 'x']);
        }

        $this->delete(route('backend.account.destroy'), ['current_password' => 'mala', 'confirmation' => 'x'])->assertStatus(429);
    }
}
