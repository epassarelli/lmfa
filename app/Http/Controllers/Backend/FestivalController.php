<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Http\Requests\FestivalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Provincia;
use App\Models\Mes;
use RealRashid\SweetAlert\Facades\Alert;

class FestivalController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->authorizeResource(Festival::class, 'festival');
  }

  public function index()
  {
    $user = Auth::user();
    $festivales = Festival::query()
      ->when($user->hasRole('colaborador'), function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->when($user->hasRole('prensa'), function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->with('user')
      ->get();

    return view('backend.festivales.index', compact('festivales'));
  }

  public function create()
  {
    $provincias = Provincia::all();
    $meses = Mes::all();
    return view('backend.festivales.create', compact('provincias', 'meses'));
  }

  public function store(FestivalRequest $request)
  {
    // dd($request->all());
    $festival = new Festival($request->validated());

    $festival->slug = Str::slug($festival->titulo);
    $festival->user_id = Auth::id();
    $festival->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
    // dd($festival);
    // Almacena la foto en disco y obtiene el nombre original del archivo
    $nombreArchivo = $request->file('foto')->store('festivales', 'public');

    // Almacena solo el nombre del archivo en el atributo 'foto' del modelo 'Noticia'
    $festival->foto = basename($nombreArchivo);
    $festival->save();

    if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
      $this->sendNotification($festival);
    }

    Alert::success('Festival creado', 'El festival ha sido creado con éxito.');
    return redirect()->route('backend.festivales.index');
  }

  public function edit(Festival $festival)
  {
    $provincias = Provincia::all();
    $meses = Mes::all();
    return view('backend.festivales.edit', compact('festival', 'provincias', 'meses'));
  }

  public function update(FestivalRequest $request, Festival $festival)
  {
    $festival->fill($request->validated());
    $festival->slug = Str::slug($festival->titulo);
    $festival->save();

    Alert::success('Festival actualizado', 'El festival ha sido actualizado con éxito.');
    return redirect()->route('backend.festivales.index');
  }

  public function destroy(Festival $festival)
  {
    $this->authorize('delete', $festival);
    $festival->delete();

    Alert::success('Festival eliminado', 'El festival ha sido eliminado con éxito.');
    return redirect()->route('backend.festivales.index');
  }

  private function sendNotification(Festival $festival)
  {
    $details = [
      'title' => 'Se ha agregado un/a Festival en el portal',
      'titulo' => $festival->titulo,
      'user' => $festival->user->name,
    ];

    Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\FestivalCreated($details));
  }
}
