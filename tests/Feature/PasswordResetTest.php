<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_reset_password_link_screen_can_be_rendered()
    {
        if (! Features::enabled(Features::resetPasswords())) {
            return $this->markTestSkipped('Password updates are not enabled.');
=======
    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
>>>>>>> dev
        }

        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

<<<<<<< HEAD
    public function test_reset_password_link_can_be_requested()
    {
        if (! Features::enabled(Features::resetPasswords())) {
            return $this->markTestSkipped('Password updates are not enabled.');
=======
    public function test_reset_password_link_can_be_requested(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
>>>>>>> dev
        }

        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

<<<<<<< HEAD
    public function test_reset_password_screen_can_be_rendered()
    {
        if (! Features::enabled(Features::resetPasswords())) {
            return $this->markTestSkipped('Password updates are not enabled.');
=======
    public function test_reset_password_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
>>>>>>> dev
        }

        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

<<<<<<< HEAD
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
=======
        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) {
>>>>>>> dev
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

<<<<<<< HEAD
    public function test_password_can_be_reset_with_valid_token()
    {
        if (! Features::enabled(Features::resetPasswords())) {
            return $this->markTestSkipped('Password updates are not enabled.');
=======
    public function test_password_can_be_reset_with_valid_token(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
>>>>>>> dev
        }

        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

<<<<<<< HEAD
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
=======
        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
>>>>>>> dev
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertSessionHasNoErrors();

            return true;
        });
    }
}
