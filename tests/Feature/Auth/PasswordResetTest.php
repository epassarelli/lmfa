<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_has_sends_password_reset_notifications_trait()
    {
        $this->assertTrue(
            method_exists(User::class, 'sendPasswordResetNotification'),
            'User model must have SendsPasswordResetNotifications trait'
        );
    }

    public function test_password_reset_email_request_with_valid_user()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post('/password/email', [
            'email' => $user->email,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status');

        Notification::assertSentTo(
            [$user],
            ResetPassword::class,
        );
    }

    public function test_password_reset_creates_token_in_database()
    {
        $user = User::factory()->create();

        $this->post('/password/email', [
            'email' => $user->email,
        ]);

        $this->assertNotNull(
            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->first()?->token
        );
    }
}
