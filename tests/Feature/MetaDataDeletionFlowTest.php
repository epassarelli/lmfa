<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\DataDeletionRequest;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MetaDataDeletionFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('data_deletion_requests')) {
            Schema::create('data_deletion_requests', function (Blueprint $table) {
                $table->id();
                $table->string('confirmation_code', 64)->unique();
                $table->string('provider', 50);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->char('external_user_hash', 64)->nullable();
                $table->string('status', 20)->default('pending');
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('error_detail', 120)->nullable();
                $table->timestamps();

                $table->index(['provider', 'status']);
                $table->unique(['provider', 'external_user_hash']);
            });
        }
    }

    /** @test */
    public function legal_pages_are_public_and_render_with_canonical_metadata(): void
    {
        foreach ([
            '/privacidad' => 'https://mifolkloreargentino.com/privacidad',
            '/condiciones' => 'https://mifolkloreargentino.com/condiciones',
            '/eliminacion-de-datos' => 'https://mifolkloreargentino.com/eliminacion-de-datos',
            '/deleteuserdata' => 'https://mifolkloreargentino.com/deleteuserdata',
        ] as $uri => $canonical) {
            $response = $this->call('GET', $uri, [], [], [], $this->serverVariables());

            $response->assertOk();
            $response->assertDontSee('login', false);
            $response->assertSee('<link rel="canonical" href="'.$canonical.'" />', false);
        }
    }

    /** @test */
    public function callback_rejects_missing_signed_request(): void
    {
        $response = $this->call('POST', '/deleteuserdata', [], [], [], $this->serverVariables());

        $response->assertStatus(422);
        $this->assertDatabaseCount('data_deletion_requests', 0);
    }

    /** @test */
    public function callback_rejects_invalid_signature(): void
    {
        config()->set('services.facebook.client_secret', 'test-secret');

        $response = $this->call('POST', '/deleteuserdata', [
            'signed_request' => $this->makeSignedRequest([
                'algorithm' => 'HMAC-SHA256',
                'user_id' => 'fb-invalid-signature',
            ], 'other-secret'),
        ], [], [], $this->serverVariables());

        $response->assertStatus(422);
        $this->assertDatabaseCount('data_deletion_requests', 0);
    }

    /** @test */
    public function callback_rejects_unsupported_algorithm(): void
    {
        config()->set('services.facebook.client_secret', 'test-secret');

        $response = $this->call('POST', '/deleteuserdata', [
            'signed_request' => $this->makeSignedRequest([
                'algorithm' => 'SHA1',
                'user_id' => 'fb-bad-algo',
            ], 'test-secret'),
        ], [], [], $this->serverVariables());

        $response->assertStatus(422);
        $this->assertDatabaseCount('data_deletion_requests', 0);
    }

    /** @test */
    public function callback_accepts_valid_signature_returns_canonical_url_and_records_request(): void
    {
        config()->set('services.facebook.client_secret', 'test-secret');

        $response = $this->call('POST', '/deleteuserdata', [
            'signed_request' => $this->makeSignedRequest([
                'algorithm' => 'HMAC-SHA256',
                'user_id' => 'fb-success-001',
            ], 'test-secret'),
        ], [], [], $this->serverVariables());

        $response->assertOk()
            ->assertJsonStructure(['url', 'confirmation_code']);

        $payload = $response->json();

        $this->assertStringStartsWith('https://mifolkloreargentino.com/deleteuserdata/status/', $payload['url']);
        $this->assertSame(
            'https://mifolkloreargentino.com/deleteuserdata/status/'.$payload['confirmation_code'],
            $payload['url']
        );

        $this->assertDatabaseHas('data_deletion_requests', [
            'provider' => 'facebook',
            'confirmation_code' => $payload['confirmation_code'],
            'status' => DataDeletionRequest::STATUS_COMPLETED,
        ]);
    }

    /** @test */
    public function callback_processes_a_facebook_linked_user_and_cleans_sensitive_associations(): void
    {
        config()->set('services.facebook.client_secret', 'test-secret');

        $user = User::factory()->create([
            'name' => 'Cuenta Facebook',
            'email' => 'facebook-user@example.com',
            'facebook_id' => 'fb-user-100',
            'google_id' => null,
            'phone' => '12345678',
        ]);

        DB::table('sessions')->insert([
            'id' => 'session-facebook-user',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $user->createToken('device-token');

        SocialAccount::query()->create([
            'owner_type' => User::class,
            'owner_id' => $user->id,
            'provider' => 'facebook',
            'account_name' => 'Cuenta principal',
            'account_external_id' => 'fb-user-100',
            'page_or_profile_name' => 'Mi perfil',
            'token_encrypted' => 'secret-token',
            'status' => 'active',
        ]);

        DB::table('news')->insert([
            'id' => 9801,
            'title' => 'Nota asociada al usuario',
            'slug' => 'nota-asociada-usuario',
            'body' => 'Contenido de prueba',
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
            'created_by' => $user->id,
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ]);

        $response = $this->call('POST', '/deleteuserdata', [
            'signed_request' => $this->makeSignedRequest([
                'algorithm' => 'HMAC-SHA256',
                'user_id' => 'fb-user-100',
            ], 'test-secret'),
        ], [], [], $this->serverVariables());

        $response->assertOk();

        $user->refresh();

        $this->assertNull($user->facebook_id);
        $this->assertSame('Usuario eliminado '.$user->id, $user->name);
        $this->assertStringContainsString('@mifolkloreargentino.invalid', $user->email);
        $this->assertNull($user->phone);
        $this->assertDatabaseMissing('social_accounts', [
            'owner_type' => User::class,
            'owner_id' => $user->id,
            'provider' => 'facebook',
        ]);
        $this->assertDatabaseMissing('sessions', [
            'id' => 'session-facebook-user',
        ]);
        $this->assertSame(0, DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $user->id)
            ->count());
        $this->assertDatabaseHas('news', [
            'id' => 9801,
            'created_by' => $user->id,
        ]);
    }

    /** @test */
    public function callback_does_not_fail_when_the_facebook_user_does_not_exist_locally(): void
    {
        config()->set('services.facebook.client_secret', 'test-secret');

        $response = $this->call('POST', '/deleteuserdata', [
            'signed_request' => $this->makeSignedRequest([
                'algorithm' => 'HMAC-SHA256',
                'user_id' => 'fb-unknown-404',
            ], 'test-secret'),
        ], [], [], $this->serverVariables());

        $response->assertOk();

        $request = DataDeletionRequest::query()->firstOrFail();

        $this->assertNull($request->user_id);
        $this->assertSame(DataDeletionRequest::STATUS_COMPLETED, $request->status);
    }

    /** @test */
    public function callback_is_idempotent_for_the_same_facebook_user(): void
    {
        config()->set('services.facebook.client_secret', 'test-secret');

        $payload = [
            'signed_request' => $this->makeSignedRequest([
                'algorithm' => 'HMAC-SHA256',
                'user_id' => 'fb-repeat-01',
            ], 'test-secret'),
        ];

        $first = $this->call('POST', '/deleteuserdata', $payload, [], [], $this->serverVariables());
        $second = $this->call('POST', '/deleteuserdata', $payload, [], [], $this->serverVariables());

        $first->assertOk();
        $second->assertOk();
        $this->assertSame($first->json('confirmation_code'), $second->json('confirmation_code'));
        $this->assertDatabaseCount('data_deletion_requests', 1);
    }

    /** @test */
    public function status_page_is_public_hides_personal_data_and_returns_404_for_unknown_code(): void
    {
        $request = DataDeletionRequest::query()->create([
            'confirmation_code' => str_repeat('a', 64),
            'provider' => 'facebook',
            'status' => DataDeletionRequest::STATUS_COMPLETED,
            'requested_at' => now()->subMinutes(5),
            'completed_at' => now(),
            'external_user_hash' => hash('sha256', 'facebook|hidden-user'),
        ]);

        $ok = $this->call('GET', '/deleteuserdata/status/'.$request->confirmation_code, [], [], [], $this->serverVariables());

        $ok->assertOk();
        $ok->assertSee($request->confirmation_code);
        $ok->assertSee('Completada');
        $ok->assertDontSee('hidden-user');
        $ok->assertDontSee('@');

        $notFound = $this->call('GET', '/deleteuserdata/status/'.str_repeat('b', 64), [], [], [], $this->serverVariables());
        $notFound->assertNotFound();
    }

    /** @test */
    public function csrf_exception_is_scoped_to_deleteuserdata_route_only(): void
    {
        $middleware = app(VerifyCsrfToken::class);
        $property = new \ReflectionProperty($middleware, 'except');
        $property->setAccessible(true);
        $except = $property->getValue($middleware);

        $this->assertContains('deleteuserdata', $except);
        $this->assertNotContains('contacto', $except);
        $this->assertTrue(Route::has('legal.deleteuserdata.callback'));
        $this->assertTrue(Route::has('contacto.store'));
    }

    private function makeSignedRequest(array $payload, string $secret): string
    {
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', $encodedPayload, $secret, true);

        return $this->base64UrlEncode($signature).'.'.$encodedPayload;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function serverVariables(): array
    {
        return [
            'HTTP_HOST' => 'mifolkloreargentino.com',
            'HTTPS' => 'on',
        ];
    }
}
