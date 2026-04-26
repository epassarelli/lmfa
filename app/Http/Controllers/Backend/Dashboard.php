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
            'contributions' => \App\Models\Contribution::where('status', 'pending')->count(),
        ];


        return view('backend.dashboard', compact('user', 'roles', 'permissions', 'contadores'));
    }
}
