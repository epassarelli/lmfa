<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class SocialiteController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return $this->redirectToProvider('google');
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        return $this->handleProviderCallback('google', 'google_id');
    }

    public function redirectToFacebook(): RedirectResponse
    {
        return $this->redirectToProvider('facebook', ['email']);
    }

    public function handleFacebookCallback(): RedirectResponse
    {
        return $this->handleProviderCallback('facebook', 'facebook_id');
    }

    private function redirectToProvider(string $provider, array $scopes = []): RedirectResponse
    {
        $driver = Socialite::driver($provider);

        if (! empty($scopes)) {
            $driver = $driver->scopes($scopes);
        }

        return $driver->redirect();
    }

    private function handleProviderCallback(string $provider, string $providerColumn): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = $this->resolveUser($socialUser, $providerColumn);

            Auth::login($user, true);

            return redirect()->intended(route('dashboard'));
        } catch (\Throwable $exception) {
            Log::error("Social login failed for {$provider}", [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => "No se pudo iniciar sesion con {$this->providerLabel($provider)}. Revisa la configuracion del proveedor e intenta nuevamente.",
                ]);
        }
    }

    private function resolveUser(SocialiteUser $socialUser, string $providerColumn): User
    {
        $providerId = $socialUser->getId();
        $email = $socialUser->getEmail();
        $name = $socialUser->getName() ?: $socialUser->getNickname() ?: 'Usuario MFA';

        $user = User::query()
            ->where($providerColumn, $providerId)
            ->when($email, fn ($query) => $query->orWhere('email', $email))
            ->first();

        if (! $user && ! $email) {
            throw new \RuntimeException('El proveedor no devolvio un email para asociar la cuenta.');
        }

        if (! $user) {
            $user = new User();
            $user->email = $email;
            $user->password = Hash::make(Str::random(40));
            $user->email_verified_at = now();
        }

        $user->name = $user->name ?: $name;
        $user->{$providerColumn} = $providerId;
        $user->last_login_at = now();
        $user->save();

        return $user;
    }

    private function providerLabel(string $provider): string
    {
        return match ($provider) {
            'google' => 'Google',
            'facebook' => 'Facebook',
            default => ucfirst($provider),
        };
    }
}
