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

        $noticias = Noticia::with('interprete', 'user')->get();
        return view('backend.noticias.index', compact('noticias'));
    }

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
        return redirect()->route('crud.noticias.index'); //->with('success', 'La noticia ha sido creada con éxito.');
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
        return redirect()->route('crud.noticias.index'); //->with('success', 'La noticia ha sido actualizada con éxito.');
    }

    public function destroy(Noticia $noticia)
    {
        $this->authorize('delete', $noticia);

        $noticia->delete();
        // Para mensajes de éxito
        Alert::success('Noticia eliminada', 'La noticia ha sido eliminada con éxito.');
        return redirect()->route('noticias.index'); //->with('success', 'La noticia ha sido eliminada con éxito.');
    }
}
