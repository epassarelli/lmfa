<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Interprete;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class InterpreteController extends Controller
{
  public function index()
  {
    $interpretes = Interprete::all();
    return view('backend.interpretes.index', compact('interpretes'));
  }

  public function create()
  {
    return view('backend.interpretes.create');
  }

  public function store(Request $request)
  {
    $request->validate([
      'interprete' => 'required|string|max:255',
      'slug' => 'required|string|max:255|unique:interpretes',
      'foto' => 'required|string|max:255',
      'biografia' => 'required',
      // 'telefono' => 'required|string|max:255',
      // 'correo' => 'required|email|max:255',
      // 'instagram' => 'nullable|string|max:255',
      // 'twitter' => 'nullable|string|max:255',
      // 'youtube' => 'nullable|string|max:255',
      // 'visitas' => 'required|integer',
      // 'publicar' => 'required|date',
      // 'estado' => 'required|integer',
      'user_id' => 'nullable|exists:users,id',
    ]);

    Interprete::create($request->all());

    return redirect()->route('backend.interpretes.index')
      ->with('success', 'Interprete creado correctamente.');
  }

  public function show(Interprete $interprete)
  {
    return view('backend.interpretes.show', compact('interprete'));
  }

  public function edit(Interprete $interprete)
  {
    return view('backend.interpretes.edit', compact('interprete'));
  }

  public function update(Request $request, Interprete $interprete)
  {
    $request->validate([
      'interprete' => 'required|string|max:255',
      'slug' => 'required|string|max:255|unique:interpretes,slug,' . $interprete->id,
      'foto' => 'image',
      'biografia' => 'required',
      // 'telefono' => 'required|string|max:255',
      // 'correo' => 'required|email|max:255',
      // 'instagram' => 'nullable|string|max:255',
      // 'twitter' => 'nullable|string|max:255',
      // 'youtube' => 'nullable|string|max:255',
      // 'visitas' => 'required|integer',
      // 'publicar' => 'required|date',
      // 'estado' => 'required|integer',
      'user_id' => 'nullable|exists:users,id',
    ]);

    // $interprete->update($request->all());

    $interprete->fill($request->all());
    if ($request->hasFile('foto')) {
      // Almacena la foto en disco y obtiene el nombre original del archivo
      $nombreArchivo = $request->file('foto')->store('interpretes', 'public');

      // Almacena solo el nombre del archivo en el atributo 'foto' del modelo 'interprete'
      $interprete->foto = basename($nombreArchivo);
    }

    dd($interprete);
    $interprete->save();


    return redirect()->route('backend.interpretes.index')
      ->with('success', 'Interprete actualizado correctamente.');
  }

  public function destroy(Interprete $interprete)
  {
    $interprete->delete();

    return redirect()->route('backend.interpretes.index')
      ->with('success', 'Interprete eliminado correctamente.');
  }
}
