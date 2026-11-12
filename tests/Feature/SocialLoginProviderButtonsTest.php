<?php

namespace Tests\Feature;

use Tests\TestCase;

class SocialLoginProviderButtonsTest extends TestCase
{
    /** @dataProvider visibility */
    public function test_provider_buttons_can_be_enabled_independently(bool $global, ?bool $google, ?bool $facebook, bool $seeGoogle, bool $seeFacebook): void
    {
        // También permite ejecutar esta prueba desde la app Docker compartida,
        // renderizando las vistas del worktree donde reside este archivo.
        $finder = app('view')->getFinder();
        $finder->prependLocation(dirname(__DIR__, 2).'/resources/views');
        $finder->flush();
        $this->assertSame(
            realpath(dirname(__DIR__, 2).'/resources/views/auth/login.blade.php'),
            realpath($finder->find('auth.login')),
        );
        config([
            'services.social_login.enabled' => $global,
            'services.social_login.google_enabled' => $google,
            'services.social_login.facebook_enabled' => $facebook,
        ]);

        foreach (['login', 'register'] as $path) {
            $response = $this->get('/'.$path)->assertOk();
            foreach (['google' => $seeGoogle, 'facebook' => $seeFacebook] as $provider => $visible) {
                if ($visible) {
                    $response->assertSee(route('auth.'.$provider), false);
                } else {
                    $response->assertDontSee(route('auth.'.$provider), false);
                }
            }
            $response->assertSee('name="email"', false)->assertSee('name="password"', false);
        }
    }

    public static function visibility(): array
    {
        return [
            'default hidden' => [false, null, null, false, false],
            'legacy global switch' => [true, null, null, true, true],
            'Google only' => [false, true, false, true, false],
            'Facebook only' => [false, false, true, false, true],
            'explicit Facebook off overrides global' => [true, null, false, true, false],
        ];
    }
}
