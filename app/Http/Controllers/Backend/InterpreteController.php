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
use Illuminate\Support\Facades\Storage;
use App\Services\ImageUploadService;

class InterpreteController extends Controller
{
  protected $imageService;

  public function __construct(ImageUploadService $imageService)
  {
    $this->middleware('auth');
    $this->imageService = $imageService;
  }

  public function index()
  {
    $user = Auth::user();
    // $interpretes = Interprete::all();
    $interpretes = Interprete::withCount(['noticias', 'shows', 'discos', 'canciones'])
      ->with(['noticias', 'shows', 'discos', 'canciones', 'images'])
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
    // Valido todos los campos que lleguen antes de procesar
    $artista = new Interprete($request->validated());

    $artista->slug = Str::slug($artista->interprete);
    $artista->user_id = Auth::id();
    $artista->estado = Auth::user()->hasRole('administrador') ? 1 : 0;

    $artista->save();

    if ($request->hasFile('foto')) {
      $this->imageService->process(
        $request->file('foto'),
        $artista,
        'artist',
        'interpretes'
      );
    }

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
    // Valido todos los campos que lleguen antes de procesar
    $interprete->fill($request->validated());
    $interprete->slug = Str::slug($interprete->interprete);

    // if ($request->hasFile('foto')) {

    //   // Crear nuevo nombre con el slug actualizado
    //   $nombreArchivo = $interprete->slug . '.' . $request->file('foto')->getClientOriginalExtension();

    //   $request->file('foto')->storeAs('interpretes', $nombreArchivo, 'public');

    //   // Almacena solo el nombre del archivo en el atributo 'foto' del modelo 'interprete'
    //   $interprete->foto = basename($nombreArchivo);
    // }


    if ($request->hasFile('foto')) {
      $this->imageService->process(
        $request->file('foto'),
        $interprete,
        'artist',
        'interpretes',
        true // Reemplazar anterior
      );
    }



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
