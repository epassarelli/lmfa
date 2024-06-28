<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\User;
use App\Http\Requests\AlbumRequest;
use App\Models\Interprete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class AlbumController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->authorizeResource(Album::class, 'album');
  }

  public function index()
  {
    $user = Auth::user();
    $albums = Album::query()
      ->when($user->hasRole('colaborador'), function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->when($user->hasRole('prensa'), function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->with('interprete', 'user')
      ->get();

    return view('backend.albunes.index', compact('albums'));
  }

  public function create()
  {
    $interpretes = Interprete::all();
    return view('backend.albunes.create', compact('interpretes'));
  }

  public function store(AlbumRequest $request)
  {
    $album = new Album($request->validated());
    $album->slug = Str::slug($album->album);
    $album->user_id = Auth::id();
    $album->estado = Auth::user()->hasRole('prensa') ? 1 : 0;
    $album->save();

    if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
      $this->sendNotification($album);
    }
    // Para mensajes de éxito
    Alert::success('Album creada', 'El album ha sido creado con éxito.');
    return redirect()->route('backend.albums.index');
  }

  public function edit(Album $album)
  {
    $interpretes = Interprete::all();
    return view('backend.albunes.edit', compact('album', 'interpretes'));
  }

  public function update(AlbumRequest $request, Album $album)
  {
    $album->fill($request->validated());
    $album->slug = Str::slug($album->album);
    $album->save();

    return redirect()->route('backend.albums.index')->with('success', 'Álbum actualizado exitosamente.');
  }

  public function destroy(Album $album)
  {
    $this->authorize('delete', $album);
    $album->delete();

    Alert::success('Album eliminado', 'El album se ha sido eliminado con éxito.');
    return redirect()->route('backend.albums.index');
  }

  private function sendNotification(Album $album)
  {
    $details = [
      'title' => 'Se ha agregado un/a Album en el portal',
      'album' => $album->album,
      'foto' => $album->foto,
      'anio' => $album->anio,
      'interprete' => $album->interprete->nombre,
      'user' => $album->user->name,
    ];

    Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\AlbumCreated($details));
  }
}
