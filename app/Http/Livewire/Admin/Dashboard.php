<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;

use App\Models\User;
use App\Models\Team;
use App\Models\Interprete;
use App\Models\Noticia;
use App\Models\Show;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Mito;
use App\Models\Festival;
use App\Models\Comida;
use App\Models\Radio;
use App\Models\Penia;

class Dashboard extends Component
{

    public function render()
    {

        $users = User::count();

        // $teams = Team::count();

        $interpretes = Interprete::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        $noticias = Noticia::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        $shows = Show::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        $discos = Album::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        $canciones = Cancion::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        $mitos = Mito::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        $festivales = Festival::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        $comidas = Comida::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        $radios = Radio::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        $penias = Penia::selectRaw('count(*) as count, estado')
            ->groupBy('estado')
            ->pluck('count', 'estado');

        return view('livewire.backend.dashboard', compact('users', 'teams', 'interpretes', 'noticias', 'shows', 'discos', 'canciones', 'mitos', 'festivales', 'comidas', 'radios', 'penias'));
    }

    // public function render()
    // {
    //     return view('livewire.backend.dashboard');
    // }

}
