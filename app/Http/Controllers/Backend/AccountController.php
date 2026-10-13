<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Services\AccountEraser;
use App\Services\AccountSummaryService;
use App\Services\UserSessionService;
use App\Support\UserRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * "Mis datos": cada persona edita sus propios datos y su contraseña, gestiona sus sesiones y puede
 * eliminar su cuenta. No recibe ningún id: solo opera sobre el usuario autenticado, y solo acepta
 * una lista cerrada de campos.
 */
class AccountController extends Controller
{
    public const DELETE_CONFIRMATION = 'ELIMINAR';

    public function __construct(
        private AccountSummaryService $summary,
        private UserSessionService $sessions,
        private AccountEraser $eraser,
    ) {
    }

    public function edit(Request $request)
    {
        $user = Auth::user();

        return view('backend.account.edit', [
            'user' => $user,
            'managed' => array_slice($this->summary->managedEntities($user), 0, 10),
            'claims' => $this->summary->claimCounts($user),
            'content' => $this->summary->content($user),
            'newsletter' => NewsletterSubscriber::query()->where('email', $user->email)->first(),
            'sessionsEnabled' => $this->sessions->enabled(),
            'sessions' => $this->sessions->list($user, $request->hasSession() ? $request->session()->getId() : null),
            'canDelete' => ! $user->hasRole('administrador'),
            'soleOwnerFichas' => $this->fichasLeftWithoutOwner($user),
            'deleteConfirmation' => self::DELETE_CONFIRMATION,
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
        $user->forceFill(['password' => Hash::make($request->string('password')->toString())])->save();

        // Cambiar la contraseña cierra el resto de las sesiones (y la cookie "recordarme").
        $closed = $this->sessions->logoutOthers($user, $this->currentSessionId($request));

        return redirect()->route('backend.account.edit')->with('success', $closed > 0
            ? "Tu contraseña se actualizó y cerramos {$closed} sesión(es) en otros dispositivos."
            : 'Tu contraseña se actualizó.');
    }

    public function logoutOtherSessions(Request $request): RedirectResponse
    {
        $request->validateWithBag('sessions', [
            'current_password' => ['required', 'current_password'],
        ], ['current_password.current_password' => 'La contraseña actual no es correcta.'], ['current_password' => 'contraseña actual']);

        $closed = $this->sessions->logoutOthers(Auth::user(), $this->currentSessionId($request));

        return redirect()->route('backend.account.edit')->with('success', $closed > 0
            ? "Cerramos {$closed} sesión(es) en otros dispositivos."
            : 'No había otras sesiones abiertas.');
    }

    public function updateNewsletter(Request $request): RedirectResponse
    {
        $data = $request->validate(['subscribe' => ['required', 'boolean']]);
        $user = Auth::user();

        $subscriptions = app(\App\Services\NewsletterSubscriptionService::class);

        if ($data['subscribe']) {
            // El email de la cuenta no está verificado: se suscribe con doble confirmación, como en el formulario público.
            $subscriptions->request($user->email, 'account', $request->ip(), $user->id);
            $message = 'Te enviamos un correo a '.$user->email.' para confirmar tu suscripción. Hasta que lo confirmes no vas a recibir el newsletter.';
        } else {
            $subscriber = NewsletterSubscriber::where('email', $user->email)->first();

            if ($subscriber) {
                $subscriptions->unsubscribe($subscriber);
            }
            $message = 'Te diste de baja del newsletter.';
        }

        return redirect()->route('backend.account.edit')->with('success', $message);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Los administradores los gestiona otro administrador (evita quedarse sin acceso al backoffice).
        abort_if($user->hasRole('administrador'), 403, 'Las cuentas de administrador no se pueden eliminar desde acá.');

        $request->validateWithBag('delete', [
            'current_password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:'.self::DELETE_CONFIRMATION],
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'confirmation.in' => 'Escribí '.self::DELETE_CONFIRMATION.' (en mayúsculas) para confirmar.',
            'confirmation.required' => 'Escribí '.self::DELETE_CONFIRMATION.' (en mayúsculas) para confirmar.',
        ], ['current_password' => 'contraseña actual', 'confirmation' => 'confirmación']);

        $this->eraser->erase($user);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Tu cuenta fue eliminada. Gracias por haber sido parte de Mi Folklore Argentino.');
    }

    private function currentSessionId(Request $request): ?string
    {
        return $request->hasSession() ? $request->session()->getId() : null;
    }

    /**
     * Cantidad de fichas que quedarían sin ningún responsable activo si esta persona se va.
     */
    private function fichasLeftWithoutOwner(User $user): int
    {
        $organizationIds = OrganizationMember::query()
            ->where('user_id', $user->id)->where('role', 'owner')->where('status', 'active')->pluck('organization_id');

        if ($organizationIds->isEmpty()) {
            return 0;
        }

        $withOtherOwner = OrganizationMember::query()
            ->whereIn('organization_id', $organizationIds)->where('role', 'owner')->where('status', 'active')->where('user_id', '!=', $user->id)
            ->pluck('organization_id');

        $soleOwnerOrganizations = $organizationIds->diff($withOtherOwner);

        return $soleOwnerOrganizations->isEmpty() ? 0 : DB::table('organization_entities')->whereIn('organization_id', $soleOwnerOrganizations)->count();
    }
}
