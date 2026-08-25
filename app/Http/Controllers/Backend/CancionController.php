<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancionRequest;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Interprete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class CancionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Cancion::class, 'cancion');
    }

    public function index(Request $request)
    {
        $status = $request->string('status')->toString();

        if (! in_array($status, ['all', 'active', 'pending'], true)) {
            $status = 'all';
        }

        return view('backend.canciones.index', compact('status'));
    }

    public function getCanciones(Request $request)
    {
        $user = $request->user();
        $status = $request->string('status')->toString();
        $statusMap = [
            'active' => 1,
            'pending' => 0,
        ];

        $canciones = Cancion::query()
            ->when($user->hasRole('colaborador') || $user->hasRole('prensa'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('interprete:id,interprete', 'user:id,name')
            ->when(array_key_exists($status, $statusMap), function ($query) use ($status, $statusMap) {
                $query->where('estado', $statusMap[$status]);
            });

        return DataTables::eloquent($canciones)
            ->filterColumn('cancion', function ($query, $keyword) {
                $query->where('cancion', 'like', "%{$keyword}%");
            })
            ->filterColumn('interprete', function ($query, $keyword) {
                $query->whereHas('interprete', function ($interpreteQuery) use ($keyword) {
                    $interpreteQuery->where('interprete', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('interprete', function ($query, $order) {
                $query->leftJoin('interpretes', 'interpretes.id', '=', 'canciones.interprete_id')
                    ->orderBy('interpretes.interprete', $order)
                    ->select('canciones.*');
            })
            ->filter(function ($query) use ($request) {
                $search = trim((string) data_get($request->input('search'), 'value', ''));

                if ($search === '') {
                    return;
                }

                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('cancion', 'like', "%{$search}%")
                        ->orWhere('visitas', 'like', "%{$search}%")
                        ->orWhereHas('interprete', function ($interpreteQuery) use ($search) {
                            $interpreteQuery->where('interprete', 'like', "%{$search}%");
                        });
                });
            })
            ->addColumn('interprete', function (Cancion $cancion) {
                return $cancion->interprete?->interprete ?? '-';
            })
            ->editColumn('visitas', function (Cancion $cancion) {
                return (string) $cancion->visitas;
            })
            ->editColumn('estado', function (Cancion $cancion) {
                return $cancion->estado == 1
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-warning">Pendiente</span>';
            })
            ->addColumn('acciones', function (Cancion $cancion) {
                $editUrl = route('backend.canciones.edit', $cancion);
                $deleteUrl = route('backend.canciones.destroy', $cancion);
                $deleteForm = csrf_field().method_field('DELETE');

                return "
                    <a href='{$editUrl}' class='btn btn-warning'>
                        <i class='fas fa-edit'></i>
                    </a>
                    <form action='{$deleteUrl}' method='POST' style='display:inline-block;' onsubmit='return confirm(\"Estas seguro de eliminar esta cancion?\")'>
                        {$deleteForm}
                        <button type='submit' class='btn btn-danger'>
                            <i class='fas fa-trash-alt'></i>
                        </button>
                    </form>
                ";
            })
            ->rawColumns(['acciones', 'estado'])
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

        Alert::success('Cancion creada', 'La cancion ha sido creada con exito.');

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

        Alert::success('Cancion actualizada', 'La cancion ha sido actualizada con exito.');

        return redirect()->route('backend.canciones.index');
    }

    public function destroy(Cancion $cancion)
    {
        if (! Auth::user()->isAdmin()) {
            return redirect()->route('backend.canciones.index')
                ->with('error', 'No tienes permiso para eliminar esta cancion.');
        }

        $cancion->delete();

        Alert::success('Cancion eliminada', 'La cancion ha sido eliminada con exito.');

        return redirect()->route('backend.canciones.index');
    }

    public function storeAjax(Request $request)
    {
        $request->validate([
            'cancion' => 'required|string|max:255',
            'interprete_id' => 'required|exists:interpretes,id',
        ]);

        $existingCancion = Cancion::where('cancion', $request->cancion)
            ->where('interprete_id', $request->interprete_id)
            ->first();

        if ($existingCancion) {
            return response()->json(['success' => false, 'message' => 'La cancion ya existe para este interprete.']);
        }

        $cancion = new Cancion();
        $cancion->cancion = $request->cancion;
        $cancion->slug = Str::slug($request->cancion);
        $cancion->letra = 'No disponible aun';
        $cancion->youtube = null;
        $cancion->spotify = null;
        $cancion->estado = 1;
        $cancion->visitas = 0;
        $cancion->interprete_id = $request->interprete_id;
        $cancion->user_id = auth()->id();
        $cancion->save();

        return response()->json(['success' => true, 'cancion' => $cancion]);
    }

    private function sendNotification(Cancion $cancion)
    {
        try {
            $details = [
                'title' => 'Se ha agregado una cancion en el portal',
                'cancion' => $cancion->cancion,
                'album' => $cancion->albunes->pluck('nombre')->join(', '),
                'interprete' => $cancion->interprete?->interprete ?? '-',
                'user' => $cancion->user?->name ?? 'Invitado',
            ];

            Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\CancionCreated($details));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de Cancion: '.$e->getMessage());
        }
    }
}
