<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cancion;
use App\Http\Requests\CancionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Interprete;
use App\Models\Album;
use RealRashid\SweetAlert\Facades\Alert;

class CancionController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->authorizeResource(Cancion::class, 'cancion');
  }

  public function index()
  {
    $user = Auth::user();
    $canciones = Cancion::query()
      ->when($user->hasRole('colaborador'), function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->when($user->hasRole('prensa'), function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->with('interprete', 'user')
      ->get();

    return view('backend.canciones.index', compact('canciones'));
  }

  public function create()
  {
    $interpretes = Interprete::all();
    return view('backend.canciones.create', compact('interpretes'));
  }

  public function store(CancionRequest $request)
  {
    $cancion = new Cancion($request->validated());
    $cancion->slug = Str::slug($cancion->cancion);
    $cancion->user_id = Auth::id();
    $cancion->estado = Auth::user()->hasRole('prensa') ? 1 : 0;
    $cancion->save();

    if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
      $this->sendNotification($cancion);
    }

    Alert::success('Canción creada', 'La canción ha sido creada con éxito.');
    return redirect()->route('backend.canciones.index');
  }

  public function edit(Cancion $cancion)
  {
    $interpretes = Interprete::all();
    $albums = Album::where('interprete_id', $cancion->interprete_id)->get();
    return view('backend.canciones.edit', compact('cancion', 'interpretes', 'albums'));
  }

  public function update(CancionRequest $request, Cancion $cancion)
  {
    $cancion->fill($request->validated());
    $cancion->slug = Str::slug($cancion->cancion);
    $cancion->save();

    Alert::success('Canción actualizada', 'La canción ha sido actualizada con éxito.');
    return redirect()->route('backend.canciones.index');
  }

  public function destroy(Cancion $cancion)
  {
    $this->authorize('delete', $cancion);
    $cancion->delete();

    Alert::success('Canción eliminada', 'La canción ha sido eliminada con éxito.');
    return redirect()->route('backend.canciones.index');
  }

  private function sendNotification(Cancion $cancion)
  {
    $details = [
      'title' => 'Se ha agregado un/a Canción en el portal',
      'cancion' => $cancion->cancion,
      'album' => $cancion->albunes->pluck('nombre')->join(', '),
      'interprete' => $cancion->interprete->nombre,
      'user' => $cancion->user->name,
    ];

    Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\CancionCreated($details));
  }
}
