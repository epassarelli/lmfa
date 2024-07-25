<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Interprete;
use App\Http\Requests\InterpreteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;

class InterpreteController extends Controller
{
  public function index()
  {
    $user = Auth::user();
    // $interpretes = Interprete::all();
    $interpretes = Interprete::withCount(['noticias', 'shows', 'discos', 'canciones'])
      ->with('noticias', 'shows', 'discos', 'canciones')
      ->orderByDesc('noticias_count')
      ->get();
    return view('backend.interpretes.index', compact('interpretes'));
  }

  public function create()
  {
    $action = 'create';
    return view('backend.interpretes.create', compact('action'));
  }

  public function store(InterpreteRequest $request)
  {
    $artista = new Interprete($request->validated());

    $artista->slug = Str::slug($artista->interprete);
    $artista->user_id = Auth::id();
    $artista->estado = Auth::user()->hasRole('administrador') ? 1 : 0;

    if ($request->hasFile('foto')) {
      $fileName = time() . '_' . $request->file('foto')->getClientOriginalName();
      $filePath = $request->file('foto')->storeAs('interpretes', $fileName, 'public');
      $artista->foto = $fileName;
    }

    $artista->save();

    return redirect()->route('backend.interpretes.index')
      ->with('success', 'Interprete creado correctamente.');
  }

  public function show(Interprete $interprete)
  {
    return view('backend.interpretes.show', compact('interprete'));
  }

  public function edit(Interprete $interprete)
  {
    $action = 'edit';
    return view('backend.interpretes.edit', compact('interprete', 'action'));
  }

  public function update(InterpreteRequest $request, Interprete $interprete)
  {

    // $interprete->update($request->all());

    $interprete->fill($request->all());

    if ($request->hasFile('foto')) {
      // Almacena la foto en disco y obtiene el nombre original del archivo
      $nombreArchivo = $request->file('foto')->store('interpretes', 'public');

      // Almacena solo el nombre del archivo en el atributo 'foto' del modelo 'interprete'
      $interprete->foto = basename($nombreArchivo);
    }

    // dd($interprete);
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
