<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
<<<<<<< HEAD
use Laravel\Jetstream\Features;
=======
>>>>>>> dev
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_confirm_password_screen_can_be_rendered()
=======
    public function test_confirm_password_screen_can_be_rendered(): void
>>>>>>> dev
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->actingAs($user)->get('/user/confirm-password');

        $response->assertStatus(200);
    }

<<<<<<< HEAD
    public function test_password_can_be_confirmed()
=======
    public function test_password_can_be_confirmed(): void
>>>>>>> dev
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/user/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

<<<<<<< HEAD
    public function test_password_is_not_confirmed_with_invalid_password()
=======
    public function test_password_is_not_confirmed_with_invalid_password(): void
>>>>>>> dev
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/user/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}
