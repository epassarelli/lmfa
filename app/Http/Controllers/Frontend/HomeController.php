<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Livewire\Backend\Interpretes;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $metaTitle = "Mi Folklore Argentino | Todo sobre Nuestras Tradiciones y Costumbres";
        $metaDescription = "Bienvenido a Mi Folklore Argentino, tu portal sobre la cultura y tradiciones de Argentina. Descubre música, danzas y más. ¡Visítanos hoy!";
        return view('frontend.home', compact('metaTitle', 'metaDescription'));
    }
}


// Como experto en Laravel y Livewire, me puedes crear un Controller "Dashboard" que renderice en un panel donde estén la cantidad de activos e inactivos:
// Users, Teams, Interpretes, Noticias, Shows, Discos, Canciones, Mitos, Festivales, Comidas, Radios y Penias. De cada una de dichas entidades quiero pasar la cantidad de activos e inactivos. Para ello, cada tabla tiene un campo "estado" en 0 o 1.