<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Interprete;

class InterpretesController extends Controller
{
    public function index()
    {
        $interpretes = Interprete::where('estado', 1)
            ->orderBy('visitas', 'desc')
            ->paginate(12);

        // dd($interpretes);
        return view('interpretes.index', compact('interpretes'));
    }
}
