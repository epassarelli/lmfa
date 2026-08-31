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
use App\Support\BackendListing;

class InterpreteController extends Controller
{
  protected $imageService;

  public function __construct(ImageUploadService $imageService)
  {
    $this->middleware('auth');
    $this->imageService = $imageService;
    $this->authorizeResource(Interprete::class, 'interprete');
  }

  public function index(Request $request)
  {
    [$sort, $direction] = BackendListing::resolveSort(
      $request,
      ['id', 'interprete', 'correo', 'visitas', 'noticias_count', 'shows_count', 'discos_count', 'canciones_count'],
      'noticias_count'
    );

    $interpretes = Interprete::withCount(['noticias', 'shows', 'discos', 'canciones'])
      ->with(['images'])
      ->when($request->filled('search'), function ($query) use ($request) {
        $search = $request->string('search')->trim()->toString();

        $query->where(function ($inner) use ($search) {
          $inner->where('interprete', 'like', '%'.$search.'%')
            ->orWhere('correo', 'like', '%'.$search.'%');
        });
      })
      ->orderBy($sort, $direction)
      ->orderByDesc('id')
      ->paginate(25)
      ->withQueryString();
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
    $payload = $request->validated();
    $artista = new Interprete($payload);

    $artista->slug = filled($payload['slug'] ?? null)
      ? Str::slug($payload['slug'])
      : Str::slug($artista->interprete);
    $artista->user_id = Auth::id();
    $artista->estado = Auth::user()->hasRole('administrador') ? 1 : 0;

    $artista->save();

    if ($request->hasFile('foto')) {
      $this->imageService->process(
        $request->file('foto'),
        $artista,
        'artist',
        'interpretes',
        false,
        $artista->slug,
        ['alt' => $artista->image_alt ?: $artista->interprete]
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
    $payload = $request->validated();
    $interprete->fill($payload);
    $interprete->slug = filled($payload['slug'] ?? null)
      ? Str::slug($payload['slug'])
      : Str::slug($interprete->interprete);

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
        true,
        $interprete->slug,
        ['alt' => $interprete->image_alt ?: $interprete->interprete]
      );
    }



    if (Auth::user()->isAdmin() || Auth::user()->hasRole('administrador')) {
        $interprete->estado = 1;
    } else {
        $interprete->estado = 0;
    }

    $interprete->save();

    if (array_key_exists('image_alt', $payload)) {
      $image = $interprete->images()->orderBy('sort_order')->first();
      if ($image) {
        $image->update(['alt' => $interprete->image_alt ?: $interprete->interprete]);
      }
    }

    return redirect()->route('backend.interpretes.index')
      ->with('success', 'Interprete actualizado correctamente.');
  }


  public function destroy(Interprete $interprete)
  {
    if (!Auth::user()->isAdmin()) {
        return redirect()->route('backend.interpretes.index')
          ->with('error', 'No tienes permiso para eliminar este contenido.');
    }

    $interprete->delete();

    return redirect()->route('backend.interpretes.index')
      ->with('success', 'Interprete eliminado correctamente.');
  }
}
