<?php

namespace Tests\Feature;

use App\Jobs\SendReengagementMailJob;
use App\Mail\ReengagementMail;
use App\Models\ReengagementMailSend;
use App\Models\User;
use App\Services\AdminAttentionService;
use App\Services\ReengagementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReengagementTest extends TestCase
{
    use DatabaseTransactions;

    private ReengagementService $service;

    protected function setUp(): void
    {
        parent::setUp();

        if (! getenv('CI')) {
            $this->assertSame('mfa_test', DB::connection()->getDatabaseName());
        }

        $this->service = app(ReengagementService::class);
        Cache::flush();
    }

    private function user(array $attrs = []): User
    {
        $user = User::factory()->create(array_merge(['status' => 'active'], $attrs));

        // Los atributos que la fábrica no conoce se fijan directo para controlar fechas y baja.
        $dates = array_intersect_key($attrs, array_flip(['created_at', 'last_login_at', 'reengagement_opt_out_at']));

        if ($dates !== []) {
            DB::table('users')->where('id', $user->id)->update($dates);
        }

        return $user->fresh();
    }

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');

        return tap($this->user(), fn ($u) => $u->assignRole('administrador'));
    }

    /** IDs elegibles entre los usuarios de la prueba (la base de test puede tener otros). */
    private function eligibleAmong(array $users): array
    {
        return $this->service->eligibleQuery()->whereIn('id', collect($users)->pluck('id'))->pluck('id')->all();
    }

    public function test_reglas_de_elegibilidad(): void
    {
        $lapsed = $this->user(['last_login_at' => now()->subDays(40), 'created_at' => now()->subYear()]);
        $never = $this->user(['last_login_at' => null, 'created_at' => now()->subDays(60)]);
        $neverNoDate = $this->user(['last_login_at' => null, 'created_at' => null]);
        $recentLogin = $this->user(['last_login_at' => now()->subDays(10), 'created_at' => now()->subYear()]);
        $newAccount = $this->user(['last_login_at' => null, 'created_at' => now()->subDays(5)]);
        $optedOut = $this->user(['last_login_at' => now()->subDays(90), 'reengagement_opt_out_at' => now()->subDay()]);
        $inactiveStatus = $this->user(['last_login_at' => now()->subDays(90), 'status' => 'banned']);
        $recentlySent = $this->user(['last_login_at' => now()->subDays(90)]);
        ReengagementMailSend::create(['batch_uuid' => 'b1', 'user_id' => $recentlySent->id, 'email' => $recentlySent->email, 'variant' => 'lapsed', 'status' => 'sent', 'created_at' => now()->subDays(5)]);
        $failedBefore = $this->user(['last_login_at' => now()->subDays(90)]);
        ReengagementMailSend::create(['batch_uuid' => 'b1', 'user_id' => $failedBefore->id, 'email' => $failedBefore->email, 'variant' => 'lapsed', 'status' => 'failed', 'created_at' => now()->subDays(5)]);
        $oldSend = $this->user(['last_login_at' => now()->subDays(200)]);
        ReengagementMailSend::create(['batch_uuid' => 'b0', 'user_id' => $oldSend->id, 'email' => $oldSend->email, 'variant' => 'lapsed', 'status' => 'sent', 'created_at' => now()->subDays(45)]);

        $all = [$lapsed, $never, $neverNoDate, $recentLogin, $newAccount, $optedOut, $inactiveStatus, $recentlySent, $failedBefore, $oldSend];
        $eligible = $this->eligibleAmong($all);

        $this->assertEqualsCanonicalizing(
            [$lapsed->id, $never->id, $neverNoDate->id, $failedBefore->id, $oldSend->id],
            $eligible
        );
        $this->assertSame('lapsed', $this->service->variantFor($lapsed));
        $this->assertSame('never', $this->service->variantFor($never));
    }

    public function test_el_envio_crea_un_registro_por_destinatario_y_no_se_repite_en_30_dias(): void
    {
        Queue::fake();
        $a = $this->user(['last_login_at' => now()->subDays(40)]);
        $b = $this->user(['last_login_at' => null, 'created_at' => now()->subDays(60)]);

        $first = $this->service->dispatchBatch($this->admin());

        $this->assertGreaterThanOrEqual(2, $first);
        $this->assertSame(2, ReengagementMailSend::whereIn('user_id', [$a->id, $b->id])->where('status', 'queued')->count());
        $this->assertSame('never', ReengagementMailSend::where('user_id', $b->id)->value('variant'));
        Queue::assertPushed(SendReengagementMailJob::class, fn ($job) => ReengagementMailSend::find($job->sendId)?->user_id === $a->id);

        $this->assertSame(0, $this->service->dispatchBatch(), 'una segunda confirmación inmediata no vuelve a enviar');
        $this->assertSame(2, ReengagementMailSend::whereIn('user_id', [$a->id, $b->id])->count());
        $this->assertFalse($this->service->isDue());
    }

    public function test_el_job_envia_una_sola_vez_y_respeta_la_baja(): void
    {
        Mail::fake();
        $user = $this->user(['last_login_at' => now()->subDays(40)]);
        $send = ReengagementMailSend::create(['batch_uuid' => 'b', 'user_id' => $user->id, 'email' => $user->email, 'variant' => 'lapsed', 'status' => 'queued']);

        (new SendReengagementMailJob($send->id))->handle();
        (new SendReengagementMailJob($send->id))->handle();

        Mail::assertSent(ReengagementMail::class, 1);
        Mail::assertSent(ReengagementMail::class, fn ($m) => $m->hasTo($user->email));
        $this->assertSame('sent', $send->fresh()->status);

        $optOut = $this->user(['last_login_at' => now()->subDays(40)]);
        $queued = ReengagementMailSend::create(['batch_uuid' => 'b', 'user_id' => $optOut->id, 'email' => $optOut->email, 'variant' => 'lapsed', 'status' => 'queued']);
        $optOut->forceFill(['reengagement_opt_out_at' => now()])->save();

        (new SendReengagementMailJob($queued->id))->handle();

        $this->assertSame('skipped', $queued->fresh()->status);
        Mail::assertSent(ReengagementMail::class, 1);
    }

    public function test_el_mail_trae_ingreso_recuperar_contrasena_y_baja(): void
    {
        $user = $this->user(['last_login_at' => now()->subDays(40), 'name' => 'Pepe']);

        $lapsed = (new ReengagementMail($user, 'lapsed'));
        $never = (new ReengagementMail($user, 'never'));

        $this->assertSame('En Mi Folklore Argentino te extrañamos', $lapsed->envelope()->subject);
        $this->assertNotSame($lapsed->envelope()->subject, $never->envelope()->subject);

        $html = $lapsed->render();
        $this->assertStringContainsString('te extrañamos', $html);
        $this->assertStringContainsString(route('login'), $html);
        $this->assertStringContainsString(route('password.request'), $html);
        $this->assertStringContainsString('reactivacion/baja/'.$user->id, $html);
        $this->assertStringContainsString('Newsletter', $html);
        $this->assertStringContainsString('Gestión de contenidos', $html);
        $this->assertArrayHasKey('List-Unsubscribe-Post', $lapsed->headers()->text);
        $this->assertStringContainsString('te está esperando', $never->render());
    }

    public function test_la_baja_exige_url_firmada_y_marca_al_usuario(): void
    {
        $user = $this->user(['last_login_at' => now()->subDays(40)]);

        $this->get(route('reengagement.unsubscribe', $user))->assertForbidden();
        $this->post(route('reengagement.unsubscribe.perform', $user))->assertForbidden();
        $this->assertNull($user->fresh()->reengagement_opt_out_at);

        $url = $this->service->unsubscribeUrl($user);
        $this->get($url)->assertOk()->assertSee('Sí, darme de baja');
        $this->post($url)->assertOk()->assertSee('no te vamos a enviar más');

        $this->assertNotNull($user->fresh()->reengagement_opt_out_at);
        $this->assertSame([], $this->eligibleAmong([$user]));
    }

    public function test_solo_administradores_acceden_y_confirman_el_envio(): void
    {
        Queue::fake();
        $common = $this->user();
        $this->actingAs($common)->get(route('backend.reengagement.index'))->assertForbidden();

        $admin = $this->admin();
        $target = $this->user(['last_login_at' => now()->subDays(40)]);

        $this->actingAs($admin)->get(route('backend.reengagement.index'))
            ->assertOk()->assertSee('Reactivación de usuarios')->assertSee('data-reengagement-due', false);

        $this->actingAs($admin)->post(route('backend.reengagement.send'))->assertSessionHasErrors('confirm');
        $this->assertSame(0, ReengagementMailSend::where('user_id', $target->id)->count());

        $this->actingAs($admin)->post(route('backend.reengagement.send'), ['confirm' => '1'])->assertSessionHas('success');
        $this->assertSame(1, ReengagementMailSend::where('user_id', $target->id)->count());

        $this->actingAs($admin)->get(route('backend.reengagement.preview', ['variant' => 'never']))->assertOk()->assertSee('te está esperando');
    }

    public function test_el_aviso_aparece_pasados_30_dias_de_la_ultima_tanda(): void
    {
        $user = $this->user(['last_login_at' => now()->subDays(100)]);
        $this->assertGreaterThan(0, $this->service->pendingCount());

        ReengagementMailSend::create(['batch_uuid' => 'x', 'user_id' => $user->id, 'email' => $user->email, 'variant' => 'lapsed', 'status' => 'sent', 'created_at' => now()->subDays(5)]);
        $this->assertSame(0, $this->service->pendingCount(), 'con una tanda reciente no hay aviso');

        ReengagementMailSend::query()->update(['created_at' => now()->subDays(31)]);
        $this->assertTrue($this->service->isDue());
        $this->assertGreaterThan(0, $this->service->pendingCount());

        app(AdminAttentionService::class)->forget();
        $this->assertGreaterThan(0, app(AdminAttentionService::class)->data()['reengagement']['count']);
    }
}
