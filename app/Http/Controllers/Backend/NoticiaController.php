<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
=======
<<<<<<< HEAD
use App\Models\Interprete;
use App\Models\Noticia;
use App\Models\User;
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
use Illuminate\Http\Request;

use App\Models\Noticia;
use App\Models\Interprete;

use Illuminate\Support\Facades\Auth;
// use RealRashid\SweetAlert\Facades\Alert;


class NoticiaController extends Controller
{
    public function index()
    {
        $noticias = Noticia::with('interprete', 'user')->get();
        return view('backend.noticias.index', compact('noticias'));
    }

    public function create()
    {
        $interpretes = Interprete::all();
        return view('backend.noticias.create', compact('interpretes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required',
            'slug' => 'required|unique:noticias',
            'noticia' => 'required',
            'foto' => 'required|image',
            'interprete_id' => 'required|exists:interpretes,id',
        ]);

        $noticia = new Noticia($request->all());
        $noticia->foto = $request->file('foto')->store('fotos', 'public');
        $noticia->user_id = Auth::id();
        $noticia->save();

        // Para mensajes de éxito
        // Alert::success('Noticia creada', 'La noticia ha sido creada con éxito.');
        return redirect()->route('noticias.index')->with('success', 'La noticia ha sido creada con éxito.');
    }

<<<<<<< HEAD
=======
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
=======
use Illuminate\Http\Request;

use App\Models\Noticia;
use App\Models\Interprete;

use Illuminate\Support\Facades\Auth;
// use RealRashid\SweetAlert\Facades\Alert;


class NoticiaController extends Controller
{
    public function index()
    {
        $noticias = Noticia::with('interprete', 'user')->get();
        return view('backend.noticias.index', compact('noticias'));
    }

    public function create()
    {
        $interpretes = Interprete::all();
        return view('backend.noticias.create', compact('interpretes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required',
            'slug' => 'required|unique:noticias',
            'noticia' => 'required',
            'foto' => 'required|image',
            'interprete_id' => 'required|exists:interpretes,id',
        ]);

        $noticia = new Noticia($request->all());
        $noticia->foto = $request->file('foto')->store('fotos', 'public');
        $noticia->user_id = Auth::id();
        $noticia->save();

        // Para mensajes de éxito
        // Alert::success('Noticia creada', 'La noticia ha sido creada con éxito.');
        return redirect()->route('noticias.index')->with('success', 'La noticia ha sido creada con éxito.');
    }

>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    public function show(Noticia $noticia)
    {
        return view('backend.noticias.show', compact('noticia'));
    }

<<<<<<< HEAD
=======
<<<<<<< HEAD
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    public function edit(Noticia $noticia)
    {
        $interpretes = Interprete::all();
        return view('backend.noticias.edit', compact('noticia', 'interpretes'));
    }

    public function update(Request $request, Noticia $noticia)
    {
        $request->validate([
            'titulo' => 'required',
            'slug' => 'required|unique:noticias,slug,' . $noticia->id,
            'noticia' => 'required',
            'foto' => 'image',
            'interprete_id' => 'required|exists:interpretes,id',
        ]);

        $noticia->fill($request->all());
        if ($request->hasFile('foto')) {
            $noticia->foto = $request->file('foto')->store('fotos', 'public');
        }
        $noticia->save();

        // Para mensajes de éxito
        // Alert::success('Noticia actualizada', 'La noticia ha sido actualizada con éxito.');
        return redirect()->route('noticias.index')->with('success', 'La noticia ha sido actualizada con éxito.');
    }

    public function destroy(Noticia $noticia)
    {
<<<<<<< HEAD
=======
        //
=======
    public function edit(Noticia $noticia)
    {
        $interpretes = Interprete::all();
        return view('backend.noticias.edit', compact('noticia', 'interpretes'));
    }

    public function update(Request $request, Noticia $noticia)
    {
        $request->validate([
            'titulo' => 'required',
            'slug' => 'required|unique:noticias,slug,' . $noticia->id,
            'noticia' => 'required',
            'foto' => 'image',
            'interprete_id' => 'required|exists:interpretes,id',
        ]);

        $noticia->fill($request->all());
        if ($request->hasFile('foto')) {
            $noticia->foto = $request->file('foto')->store('fotos', 'public');
        }
        $noticia->save();

        // Para mensajes de éxito
        // Alert::success('Noticia actualizada', 'La noticia ha sido actualizada con éxito.');
        return redirect()->route('noticias.index')->with('success', 'La noticia ha sido actualizada con éxito.');
    }

    public function destroy(Noticia $noticia)
    {
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
        $noticia->delete();
        // Para mensajes de éxito
        // Alert::success('Noticia eliminada', 'La noticia ha sido eliminada con éxito.');
        return redirect()->route('noticias.index')->with('success', 'La noticia ha sido eliminada con éxito.');
<<<<<<< HEAD
=======
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    }
}
