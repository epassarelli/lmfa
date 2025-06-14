<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompartirController extends Controller
{
    //

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_contenido' => 'required|string',
            'contenido_id'   => 'required|integer',
            'titulo'         => 'required|string',
            'url'            => 'required|url',
            'red'            => 'required|string',
        ]);

        \App\Models\ShareLog::create([
            ...$data,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['ok' => true]);
    }
}
