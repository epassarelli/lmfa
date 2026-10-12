<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Comida;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\News;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        // $this->authorizeResource(Album::class, 'album');
    }

    public function index()
    {
        $user = Auth::user();

        // Quien no es administrador ve "Mi panel": sus fichas, solicitudes y contenido, no los totales
        // globales del portal ni enlaces a secciones que no puede abrir.
        if (! $user->hasRole('administrador')) {
            $summary = app(\App\Services\AccountSummaryService::class);

            return view('backend.dashboard-member', [
                'user' => $user,
                'entities' => $summary->managedEntities($user, 8),
                'claims' => $summary->claimCounts($user),
                'content' => $summary->content($user),
            ]);
        }

        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        // Traer las cantidades de cada entidad con la nueva nomenclatura
        $contadores = [
            'news' => News::count(),
            'canciones' => Cancion::count(),
            'usuarios' => User::count(),
            'interpretes' => Interprete::count(),
            'events' => Event::count(),
            'discos' => Album::count(),
            'comidas' => Comida::count(),
            'festivales' => Festival::count(),
        ];


        $isAdmin = $user->hasRole('administrador');
        $attention = null;
        $cards = [];

        // "Requiere tu atención": solo administradores (datos cacheados 30 s, ver AdminAttentionService).
        if ($isAdmin) {
            $service = app(\App\Services\AdminAttentionService::class);
            $attention = $service->data();
            $cards = $service->cards();
        }

        return view('backend.dashboard', compact('user', 'roles', 'permissions', 'contadores', 'isAdmin', 'attention', 'cards'));
    }
}
