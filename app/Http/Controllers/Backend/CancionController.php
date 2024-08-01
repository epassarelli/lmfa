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
use Yajra\DataTables\Facades\DataTables;

class CancionController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    // $this->authorizeResource(Cancion::class, 'cancion');
  }

  public function index()
  {
    return view('backend.canciones.index');
  }




  public function getCanciones(Request $request)
  {
    $user = $request->user();

    $canciones = Cancion::query()
      ->when($user->hasRole('colaborador') || $user->hasRole('prensa'), function ($query) use ($user) {
        $query->where('user_id', $user->id);
      })
      ->with('interprete:id,interprete', 'user:id,name');

    return DataTables::of($canciones)
      ->addColumn('interprete', function ($cancion) {
        return $cancion->interprete->interprete;
      })
      ->addColumn('acciones', function ($cancion) {
        $editUrl = route('backend.canciones.edit', $cancion);
        $deleteUrl = route('backend.canciones.destroy', $cancion);
        $deleteForm = csrf_field() . method_field('DELETE');

        return "
                <a href='{$editUrl}' class='btn btn-warning'>
                    <i class='fas fa-edit'></i>
                </a>
                <form action='{$deleteUrl}' method='POST' style='display:inline-block;' onsubmit='return confirm(\"¿Estás seguro de eliminar esta canción?\")'>
                    {$deleteForm}
                    <button type='submit' class='btn btn-danger'>
                        <i class='fas fa-trash-alt'></i>
                    </button>
                </form>
            ";
      })
      ->filter(function ($query) use ($request) {
        if ($request->has('search') && $request->search['value'] !== null) {
          $search = $request->search['value'];
          $query->where(function ($query) use ($search) {
            $query->where('cancion', 'like', "%{$search}%")
              ->orWhereHas('interprete', function ($query) use ($search) {
                $query->where('interprete', 'like', "%{$search}%");
              });
          });
        }
      })
      ->rawColumns(['acciones'])
      ->make(true);
  }




  public function create()
  {
    $action = 'create';
    $interpretes = Interprete::active()->get();
    return view('backend.canciones.create', compact('interpretes', 'action'));
  }

  public function store(CancionRequest $request)
  {
    $cancion = new Cancion($request->validated());
    $cancion->slug = Str::slug($cancion->cancion);
    $cancion->user_id = Auth::id();
    $cancion->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
    $cancion->visitas = 0;
    $cancion->save();

    if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
      $this->sendNotification($cancion);
    }

    Alert::success('Canción creada', 'La canción ha sido creada con éxito.');
    return redirect()->route('backend.canciones.index');
  }

  public function edit(Cancion $cancion)
  {
    $action = 'edit';
    $interpretes = Interprete::active()->get();
    $albums = Album::where('interprete_id', $cancion->interprete_id)->get();
    return view('backend.canciones.edit', compact('cancion', 'interpretes', 'albums', 'action'));
  }

  public function update(CancionRequest $request, Cancion $cancion)
  {
    $cancion->fill($request->validated());

    $cancion->slug = Str::slug($cancion->cancion);
    $cancion->user_id = Auth::id();
    $cancion->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
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
