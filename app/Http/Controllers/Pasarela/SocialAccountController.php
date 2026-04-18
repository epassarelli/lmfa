<?php

namespace App\Http\Controllers\Pasarela;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PC-06-HU-01: Gestión de cuentas sociales conectadas al publicador.
 *
 * Permite al usuario registrar, visualizar y desconectar cuentas de redes
 * sociales que serán usadas por la pasarela de distribución de contenido.
 *
 * No realiza OAuth en esta iteración: las credenciales se guardan
 * manualmente (token manual) para MVP. OAuth puede agregarse en fase posterior.
 */
class SocialAccountController extends Controller
{
    /** Proveedores soportados en MVP */
    const SUPPORTED_PROVIDERS = [
        'facebook'  => 'Facebook (Página)',
        'instagram' => 'Instagram (Cuenta Profesional)',
        'telegram'  => 'Telegram (Canal / Grupo)',
    ];

    /**
     * Mostrar cuentas conectadas del usuario autenticado.
     */
    public function index()
    {
        $user = Auth::user();

        $accounts = SocialAccount::where('owner_type', get_class($user))
            ->where('owner_id', $user->id)
            ->orderBy('provider')
            ->get();

        return view('pasarela.social_accounts.index', [
            'accounts'   => $accounts,
            'providers'  => self::SUPPORTED_PROVIDERS,
        ]);
    }

    /**
     * Guardar una nueva cuenta social para el usuario autenticado.
     *
     * MVP: el token se ingresa manualmente.
     * Fase posterior: reemplazar por flujo OAuth por proveedor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider'             => ['required', 'string', 'in:' . implode(',', array_keys(self::SUPPORTED_PROVIDERS))],
            'account_name'         => ['required', 'string', 'max:255'],
            'account_external_id'  => ['required', 'string', 'max:255'],
            'page_or_profile_name' => ['nullable', 'string', 'max:255'],
            'token'                => ['required', 'string', 'max:2000'],
            'scopes'               => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        // Evitar duplicar la misma cuenta (mismo provider + external_id)
        $exists = SocialAccount::where('owner_type', get_class($user))
            ->where('owner_id', $user->id)
            ->where('provider', $validated['provider'])
            ->where('account_external_id', $validated['account_external_id'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Ya existe una cuenta conectada para ese proveedor con ese ID externo.');
        }

        $scopes = null;
        if (!empty($validated['scopes'])) {
            $scopes = array_map('trim', explode(',', $validated['scopes']));
        }

        SocialAccount::create([
            'owner_type'           => get_class($user),
            'owner_id'             => $user->id,
            'provider'             => $validated['provider'],
            'account_name'         => $validated['account_name'],
            'account_external_id'  => $validated['account_external_id'],
            'page_or_profile_name' => $validated['page_or_profile_name'] ?? null,
            'token_encrypted'      => $validated['token'],
            'scopes_json'          => $scopes,
            'status'               => 'active',
        ]);

        return redirect()
            ->route('pasarela.social-accounts.index')
            ->with('success', 'Cuenta social conectada correctamente.');
    }

    /**
     * Desconectar (eliminar) una cuenta social.
     *
     * Solo el propietario puede desconectar su propia cuenta.
     */
    public function destroy(SocialAccount $socialAccount)
    {
        $user = Auth::user();

        // Autorización: sólo el dueño puede eliminarla
        if ($socialAccount->owner_type !== get_class($user) || $socialAccount->owner_id !== $user->id) {
            abort(403, 'No tenés permiso para desconectar esta cuenta.');
        }

        $socialAccount->delete();

        return redirect()
            ->route('pasarela.social-accounts.index')
            ->with('success', 'Cuenta social desconectada.');
    }
}
