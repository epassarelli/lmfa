<?php

namespace App\Http\Controllers;

use App\Models\Interprete;
use Illuminate\Http\Request;

class InterpretesCRUD extends Controller
{
    public function index()
    {
        $interpretes = Interprete::all();
        return view('interpretes.index', compact('interpretes'));
    }

    public function create()
    {
        return view('interpretes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'interprete' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:interpretes',
            'foto' => 'required|string|max:255',
            'biografia' => 'required',
            'telefono' => 'required|string|max:255',
            'correo' => 'required|email|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'visitas' => 'required|integer',
            'publicar' => 'required|date',
            'estado' => 'required|integer',
            'user_id' => 'nullable|exists:users,id',
        ]);

        Interprete::create($request->all());

        return redirect()->route('interpretes.index')
            ->with('success', 'Interprete creado correctamente.');
    }

    public function show(Interprete $interprete)
    {
        return view('interpretes.show', compact('interprete'));
    }

    public function edit(Interprete $interprete)
    {
        return view('interpretes.edit', compact('interprete'));
    }

    public function update(Request $request, Interprete $interprete)
    {
        $request->validate([
            'interprete' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:interpretes,slug,' . $interprete->id,
            'foto' => 'required|string|max:255',
            'biografia' => 'required',
            'telefono' => 'required|string|max:255',
            'correo' => 'required|email|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'visitas' => 'required|integer',
            'publicar' => 'required|date',
            'estado' => 'required|integer',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $interprete->update($request->all());

        return redirect()->route('interpretes.index')
            ->with('success', 'Interprete actualizado correctamente.');
    }

    public function destroy(Interprete $interprete)
    {
        $interprete->delete();

        return redirect()->route('interpretes.index')
            ->with('success', 'Interprete eliminado correctamente.');
    }
}
