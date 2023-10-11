<?php

namespace App\Http\Controllers;

use App\Http\Livewire\Backend\Interpretes;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }
}


// Como experto en Laravel y Livewire, me puedes crear un Controller "Dashboard" que renderice en un panel donde estén la cantidad de activos e inactivos:
// Users, Teams, Interpretes, Noticias, Shows, Discos, Canciones, Mitos, Festivales, Comidas, Radios y Penias. De cada una de dichas entidades quiero pasar la cantidad de activos e inactivos. Para ello, cada tabla tiene un campo "estado" en 0 o 1.