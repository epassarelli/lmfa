<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlbumRequest;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\User;
use App\Models\Interprete;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class AlbumController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
    // $this->authorizeResource(Album::class, 'album');
  }

  public function index()
  {
    $user = Auth::user();

    $albums = Album::query()
      ->when($user->hasRole('colaborador') || $user->hasRole('prensa'), function ($query) use ($user) {
        $query->where(
          'user_id',
          $user->id
        );
      })
      ->with('interprete', 'user')
      ->withCount('canciones') // Cuenta las canciones del álbum
      // ->withCount(['interprete as interprete_canciones_count' => function ($query) {
      //   $query->select(DB::raw('count(*)'))
      //     ->from('canciones')
      //     ->whereColumn('canciones.interprete_id', 'albums.interprete_id');
      // }])
      ->get();

    return view('backend.albunes.index', compact('albums'));
  }

  public function create()
  {
    $action = 'create';
    $interpretes = Interprete::active()->get();
    return view('backend.albunes.create', compact('interpretes', 'action'));
  }

  public function store(AlbumRequest $request)
  {
    $album = new Album($request->validated());

    $album->slug = Str::slug($album->album);
    $album->user_id = Auth::id();
    $album->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
    $album->visitas = 0;

    if ($request->hasFile('foto')) {
      $fileName = time() . '_' . $request->file('foto')->getClientOriginalName();
      $filePath = $request->file('foto')->storeAs('albunes', $fileName, 'public');
      $album->foto = $fileName;
    }

    $album->save();

    if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
      $this->sendNotification($album);
    }
    // Para mensajes de éxito
    Alert::success('Album creado', 'El album ha sido creado con éxito.');
    return redirect()->route('backend.discos.index');
  }

  public function edit(Album $album)
  {
    $action = 'edit';
    $interpretes = Interprete::active()->get();

    $interprete_id = $album->interprete_id;

    $album_canciones_ids = $album->canciones->pluck('id')->toArray();
    $canciones = Cancion::where('interprete_id', $interprete_id)
      ->whereNotIn('id', $album_canciones_ids)
      ->orderBy('cancion', 'asc')
      ->get();
    $album_canciones = $album->canciones;

    return view('backend.albunes.edit', compact('album', 'canciones', 'album_canciones', 'interpretes', 'action'));
  }

  public function update(AlbumRequest $request, Album $album)
  {
    $album->fill($request->validated());

    $album->slug = Str::slug($album->album);
    $album->user_id = Auth::id();
    $album->estado = Auth::user()->hasRole('administrador') ? 1 : 0;

    if ($request->hasFile('foto')) {
      $fileName = time() . '_' . $request->file('foto')->getClientOriginalName();
      $filePath = $request->file('foto')->storeAs('albunes', $fileName, 'public');
      $album->foto = $fileName;
    }
    $canciones = $request->input('canciones', []);
    $ordenes = $request->input('ordenes', []);

    $syncData = [];
    foreach ($canciones as $index => $cancion_id) {
      $syncData[$cancion_id] = ['orden' => $ordenes[$index]];
    }

    $album->canciones()->sync($syncData);
    $album->save();

    return redirect()->route('backend.discos.index')->with('success', 'Álbum actualizado exitosamente.');
  }

  public function destroy(Album $album)
  {
    $this->authorize('delete', $album);
    $album->delete();

    Alert::success('Album eliminado', 'El album se ha sido eliminado con éxito.');
    return redirect()->route('backend.discos.index');
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
