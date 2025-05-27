<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
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


    public function store(Request $request)
    {
        $this->authorize('create', Noticia::class);

        $request->validate([
            'titulo' => 'required',
            'slug' => 'required|unique:noticias',
            'noticia' => 'required',
            'foto' => 'required|image',
            'interprete_id' => 'required|array', // Ahora se espera un array de IDs
            'interprete_id.*' => 'exists:interpretes,id', // Validar cada ID del array
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        $noticia = new Noticia($request->except('interprete_id')); // Excluir interprete_id temporalmente

        // Almacenar la foto en disco
        $nombreArchivo = $request->file('foto')->store('noticias', 'public');
        $noticia->foto = basename($nombreArchivo);
        $noticia->user_id = Auth::id();
        $noticia->save();

        // Asignar los intérpretes a la noticia
        $noticia->interpretes()->attach($request->interprete_id);

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


    public function update(Request $request, Noticia $noticia)
    {
        $this->authorize('update', $noticia);

        $request->validate([
            'titulo' => 'required',
            'slug' => 'required|unique:noticias,slug,' . $noticia->id,
            'noticia' => 'required',
            'foto' => 'image',
            'interprete_id' => 'required|array', // Ahora se espera un array de IDs
            'interprete_id.*' => 'exists:interpretes,id', // Validar cada ID del array
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        $noticia->fill($request->except('interprete_id')); // Excluir interprete_id temporalmente

        if ($request->hasFile('foto')) {
            // Almacenar la nueva foto
            $nombreArchivo = $request->file('foto')->store('noticias', 'public');
            $noticia->foto = basename($nombreArchivo);
        }

        $noticia->save();

        // Sincronizar los intérpretes (elimina los que no están en el array y agrega los nuevos)
        $noticia->interpretes()->sync($request->interprete_id);

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
