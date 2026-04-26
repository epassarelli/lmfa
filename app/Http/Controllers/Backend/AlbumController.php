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
use App\Services\ImageUploadService;

class AlbumController extends Controller
{

  protected $imageService;

  public function __construct(ImageUploadService $imageService)
  {
    $this->middleware('auth');
    $this->imageService = $imageService;
    $this->authorizeResource(Album::class, 'album');
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
      ->with('interprete', 'user', 'images')
      ->withCount('canciones') // Cuenta las canciones del álbum
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

    // Trar el slug del interprete y concatenarlo delante del album
    $album->save();

    if ($request->hasFile('foto')) {
      $this->imageService->process(
        $request->file('foto'),
        $album,
        'album',
        'albunes'
      );
    }

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
      $this->imageService->process(
        $request->file('foto'),
        $album,
        'album',
        'albunes',
        true
      );
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
    if (!Auth::user()->isAdmin()) {
        return redirect()->route('backend.discos.index')
          ->with('error', 'No tienes permiso para eliminar este disco.');
    }
    
    $album->delete();

    Alert::success('Album eliminado', 'El album se ha sido eliminado con éxito.');
    return redirect()->route('backend.discos.index');
  }

  private function sendNotification(Album $album)
  {
    try {
        $details = [
          'title' => 'Se ha agregado un/a Album en el portal',
          'album' => $album->album,
          'foto' => $album->foto,
          'anio' => $album->anio,
          'interprete' => $album->interprete?->nombre ?? '—',
          'user' => $album->user?->name ?? 'Invitado',
        ];

        Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\AlbumCreated($details));
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Error enviando correo de Album: " . $e->getMessage());
    }
  }
}
