<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\AccountSummaryService;
use App\Support\UserRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * "Mis datos": cada persona edita sus propios datos y su contraseña. No recibe ningún id: solo opera
 * sobre el usuario autenticado, y solo acepta una lista cerrada de campos.
 */
class AccountController extends Controller
{
    public function __construct(private AccountSummaryService $summary)
    {
    }

    public function edit()
    {
        $user = Auth::user();

        return view('backend.account.edit', [
            'user' => $user,
            'managed' => array_slice($this->summary->managedEntities($user), 0, 10),
            'claims' => $this->summary->claimCounts($user),
            'content' => $this->summary->content($user),
            'newsletter' => NewsletterSubscriber::query()->where('email', $user->email)->first(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('profile', [
            'name' => UserRules::name(),
            'phone' => UserRules::phone(),
        ], [], ['name' => 'nombre', 'phone' => 'teléfono']);

        // Solo name y phone: el email y el resto de los campos del usuario no se modifican desde acá.
        Auth::user()->forceFill(['name' => $data['name'], 'phone' => $data['phone'] ?? null])->save();

        return redirect()->route('backend.account.edit')->with('success', 'Tus datos se actualizaron.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => array_merge(UserRules::newPassword(), ['different:current_password']),
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.different' => 'La contraseña nueva debe ser distinta de la actual.',
        ], ['current_password' => 'contraseña actual', 'password' => 'contraseña nueva']);

        $user = Auth::user();
        $user->forceFill(['password' => Hash::make($request->string('password')->toString()), 'remember_token' => Str::random(60)])->save();

        return redirect()->route('backend.account.edit')->with('success', 'Tu contraseña se actualizó.');
    }

    public function updateNewsletter(Request $request): RedirectResponse
    {
        $data = $request->validate(['subscribe' => ['required', 'boolean']]);
        $user = Auth::user();

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $user->email]);

        if ($data['subscribe']) {
            $subscriber->status = 'active';
            $subscriber->unsubscribed_at = null;
            $subscriber->user_id = $user->id;
            $subscriber->source = $subscriber->exists ? $subscriber->source : 'account';
            $subscriber->save();
            $message = 'Quedaste suscripto al newsletter.';
        } else {
            if ($subscriber->exists && $subscriber->status === 'active') {
                $subscriber->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
            }
            $message = 'Te diste de baja del newsletter.';
        }

        return redirect()->route('backend.account.edit')->with('success', $message);
    }
}
