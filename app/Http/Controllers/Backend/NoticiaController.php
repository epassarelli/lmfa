<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Noticia;
use App\Models\Interprete;

use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;

class NoticiaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->authorize('access', Noticia::class);
        // $this->middleware('permission:access noticia');
    }


    public function index()
    {
        $this->authorize('viewAny', Noticia::class);

        $noticias = Noticia::select('id', 'titulo', 'foto', 'created_at', 'visitas')
            ->with(['interprete:id,nombre', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('backend.noticias.index', compact('noticias'));
    }

    // public function index(Request $request)
    // {
    //     $this->authorize('viewAny', Noticia::class);

    //     if ($request->ajax()) {
    //         $data = Noticia::with(['interprete:id,interprete', 'user:id,name'])->orderBy('created_at', 'desc');
    //         return DataTables::of($data)
    //             ->addColumn('interprete', function ($row) {
    //                 return $row->interprete ? $row->interprete->interprete : 'Sin intérprete';
    //             })
    //             ->addColumn('user', function ($row) {
    //                 return $row->user->name;
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $editUrl = route('backend.noticias.edit', $row->id);
    //                 $deleteUrl = route('backend.noticias.destroy', $row->id);
    //                 return '
    //                     <a href="' . $editUrl . '" class="btn btn-warning">
    //                         <i class="fas fa-edit"></i>
    //                     </a>
    //                     <form action="' . $deleteUrl . '" method="POST" style="display:inline-block;">
    //                         ' . csrf_field() . '
    //                         ' . method_field('DELETE') . '
    //                         <button type="submit" class="btn btn-danger" onclick="return confirm(\'¿Estás seguro de eliminar esta noticia?\')">
    //                             <i class="fas fa-trash-alt"></i>
    //                         </button>
    //                     </form>
    //                 ';
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     return view('backend.noticias.index');
    // }


    public function create()
    {
        $this->authorize('create', Noticia::class);

        $interpretes = Interprete::all();
        return view('backend.noticias.create', compact('interpretes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Noticia::class);

        $request->validate([
            'titulo' => 'required',
            'slug' => 'required|unique:noticias',
            'noticia' => 'required',
            'foto' => 'required|image',
            'interprete_id' => 'required|exists:interpretes,id',
        ]);

        $noticia = new Noticia($request->all());

        // Almacena la foto en disco y obtiene el nombre original del archivo
        $nombreArchivo = $request->file('foto')->store('noticias', 'public');

        // Almacena solo el nombre del archivo en el atributo 'foto' del modelo 'Noticia'
        $noticia->foto = basename($nombreArchivo);
        $noticia->user_id = Auth::id();
        $noticia->save();

        // Para mensajes de éxito
        Alert::success('Noticia creada', 'La noticia ha sido creada con éxito.');
        return redirect()->route('backend.noticias.index'); //->with('success', 'La noticia ha sido creada con éxito.');
    }

    public function show(Noticia $noticia)
    {
        $this->authorize('view', $noticia);

        return view('backend.noticias.show', compact('noticia'));
    }

    public function edit(Noticia $noticia)
    {
        $this->authorize('update', $noticia);

        $interpretes = Interprete::all();
        return view('backend.noticias.edit', compact('noticia', 'interpretes'));
    }

    public function update(Request $request, Noticia $noticia)
    {
        $this->authorize('update', $noticia);

        $request->validate([
            'titulo' => 'required',
            'slug' => 'required|unique:noticias,slug,' . $noticia->id,
            'noticia' => 'required',
            'foto' => 'image',
            'interprete_id' => 'required|exists:interpretes,id',
        ]);

        $noticia->fill($request->all());
        if ($request->hasFile('foto')) {
            // Almacena la foto en disco y obtiene el nombre original del archivo
            $nombreArchivo = $request->file('foto')->store('noticias', 'public');

            // Almacena solo el nombre del archivo en el atributo 'foto' del modelo 'Noticia'
            $noticia->foto = basename($nombreArchivo);
        }
        $noticia->save();

        // Para mensajes de éxito
        Alert::success('Noticia actualizada', 'La noticia ha sido actualizada con éxito.');
        return redirect()->route('backend.noticias.index'); //->with('success', 'La noticia ha sido actualizada con éxito.');
    }

    public function destroy(Noticia $noticia)
    {
        $this->authorize('delete', $noticia);

        $noticia->delete();
        // Para mensajes de éxito
        Alert::success('Noticia eliminada', 'La noticia ha sido eliminada con éxito.');
        return redirect()->route('backend.noticias.index'); //->with('success', 'La noticia ha sido eliminada con éxito.');
    }
}
