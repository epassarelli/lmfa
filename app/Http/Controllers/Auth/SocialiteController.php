<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class SocialiteController extends Controller
{

    /**
     * Redirecciona al usuario a la página de Google para autenticarse.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtiene la información del usuario de Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                Auth::login($user);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    // Puedes generar una contraseña aleatoria o dejarla vacía.
                    'password' => encrypt('random_password'),
                ]);

                Auth::login($user);
            }

            return redirect()->intended('admin');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['login' => 'Algo salió mal al intentar iniciar sesión con Google.']);
        }
    }



    /**
     * Redirecciona al usuario a la página de Facebook para autenticarse.
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtiene la información del usuario de Facebook.
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            $user = User::where('email', $facebookUser->getEmail())->first();

            if ($user) {
                Auth::login($user);
            } else {
                $user = User::create([
                    'name' => $facebookUser->getName(),
                    'email' => $facebookUser->getEmail(),
                    'facebook_id' => $facebookUser->getId(),
                    'password' => encrypt('random_password'),
                ]);

                Auth::login($user);
            }

            return redirect()->intended('admin');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['login' => 'Algo salió mal al intentar iniciar sesión con Facebook.']);
        }
    }
}
