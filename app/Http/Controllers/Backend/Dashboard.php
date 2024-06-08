<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Dashboard extends Controller
{
    public function index()
    {
        //$noticias = Noticia::with('interprete', 'user')->get();
        return view('backend.dashboard');
    }
}
