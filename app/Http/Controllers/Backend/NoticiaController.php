<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoticiaRequest;

use App\Models\Categoria;
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

        $noticias = Noticia::with(['interpretes:id,interprete', 'user:id,name', 'categoria:id,nombre'])
            ->orderBy('publicar', 'desc')
            ->get();

        // dd($noticias);

        return view('backend.noticias.index', compact('noticias'));
    }


    public function create()
    {
        $this->authorize('create', Noticia::class);

        $categorias = Categoria::all();
        $interpretes = Interprete::active()->get();

        return view('backend.noticias.create', compact('interpretes', 'categorias'));
    }


    // public function store(Request $request)
    // {
    //     $this->authorize('create', Noticia::class);

    //     $request->validate([
    //         'titulo' => 'required',
    //         'slug' => 'required|unique:noticias',
    //         'noticia' => 'required',
    //         'foto' => 'required|image',
    //         'interprete_id' => 'required|array', // Ahora se espera un array de IDs
    //         'interprete_id.*' => 'exists:interpretes,id', // Validar cada ID del array
    //         'categoria_id' => 'required|exists:categorias,id',
    //     ]);

    //     $noticia = new Noticia($request->except('interprete_id')); // Excluir interprete_id temporalmente

    //     // Almacenar la foto en disco
    //     $nombreArchivo = $request->file('foto')->store('noticias', 'public');
    //     $noticia->foto = basename($nombreArchivo);
    //     $noticia->user_id = Auth::id();
    //     $noticia->save();

    //     // Asignar los intérpretes a la noticia
    //     $noticia->interpretes()->attach($request->interprete_id);

    //     Alert::success('Noticia creada', 'La noticia ha sido creada con éxito.');
    //     return redirect()->route('backend.noticias.index');
    // }

    public function store(NoticiaRequest $request)
    {
        $data = $request->validated();

        // Remover foto de $data para manejarla por separado
        unset($data['foto']);

        $noticia = new Noticia();
        $noticia->fill($data);
        $noticia->interprete_id = $data['interprete_principal_id'] ?? null;
        $noticia->user_id = auth()->id();

        // Guardar fecha de publicación si existe
        if (!empty($data['publicar'])) {
            $noticia->publicar = $data['publicar'];
        }

        // Guardar imagen si fue cargada
        if ($request->hasFile('foto')) {
            $nombreArchivo = $request->file('foto')->store('noticias', 'public');
            $noticia->foto = basename($nombreArchivo);
        }

        $noticia->save();

        if (!empty($data['interprete_secundarios'])) {
            $noticia->interpretes()->sync($data['interprete_secundarios']);
        }

        Alert::success('Noticia creada', 'La noticia ha sido creada con éxito.');
        return redirect()->route('backend.noticias.index');
    }


    public function show(Noticia $noticia)
    {
        $this->authorize('view', $noticia);

        return view('backend.noticias.show', compact('noticia'));
    }


    public function edit(Noticia $noticia)
    {
        $this->authorize('update', $noticia);

        $categorias = Categoria::all();
        $interpretes = Interprete::active()->get();

        return view('backend.noticias.edit', compact('noticia', 'interpretes', 'categorias'));
    }


    public function update(NoticiaRequest $request, Noticia $noticia)
    {
        $this->authorize('update', $noticia);

        $data = $request->validated();

        // Cargar campos básicos
        $noticia->fill($data);
        $noticia->interprete_id = $data['interprete_principal_id'] ?? null;

        // Guardar fecha de publicación si existe
        if (!empty($data['publicar'])) {
            $noticia->publicar = $data['publicar'];
        }

        // Actualizar imagen si fue cargada
        if ($request->hasFile('foto')) {
            $nombreArchivo = $request->file('foto')->store('noticias', 'public');
            $noticia->foto = basename($nombreArchivo);
        }

        $noticia->save();

        // Sincronizar intérpretes secundarios (tabla pivote)
        $noticia->interpretes()->sync($data['interprete_secundarios'] ?? []);

        Alert::success('Noticia actualizada', 'La noticia ha sido actualizada con éxito.');
        return redirect()->route('backend.noticias.index');
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
