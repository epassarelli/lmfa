<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SocialLoginButtonsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_and_register_hide_social_buttons_by_default(): void
    {
        $this->assertFalse((bool) config('services.social_login.enabled'));

        foreach (['login', 'register'] as $path) {
            $this->get('/'.$path)->assertOk()
                ->assertDontSee('con Google')
                ->assertDontSee('con Facebook')
                ->assertDontSee(route('auth.google'), false)
                ->assertDontSee(route('auth.facebook'), false);
        }
    }

    public function test_switch_brings_the_buttons_back_without_code_changes(): void
    {
        config(['services.social_login.enabled' => true]);

        $this->get('/login')->assertOk()->assertSee('Ingresar con Google')->assertSee('Ingresar con Facebook');
        $this->get('/register')->assertOk()->assertSee('Registrarme con Google')->assertSee('Registrarme con Facebook');
    }

    public function test_email_and_password_login_and_registration_forms_still_work(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="email"', false)->assertSee('name="password"', false);
        $this->get('/register')->assertOk()->assertSee('name="email"', false)->assertSee('name="password"', false);

        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_social_routes_are_kept_for_when_the_integration_is_fixed(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('auth.google'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('auth.facebook'));
    }
}
