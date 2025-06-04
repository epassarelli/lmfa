<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Show;
use App\Models\Interprete;
use App\Http\Requests\ShowRequest;
use App\Models\Provincia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

use Illuminate\Http\Request;

class ShowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Show::class, 'show');
    }


    public function index()
    {
        $user = Auth::user();
        $shows = Show::query()
            ->when($user->hasRole(['colaborador', 'prensa']), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('fecha', '>=', now())
            ->with('user', 'interprete')
            ->get();

        return view('backend.shows.index', compact('shows'));
    }


    public function create()
    {
        // Obtener los intérpretes activos ordenados y con los campos necesarios
        $show = new Show();

        $interpretes = Interprete::active()->get();
        $provincias = Provincia::all();

        $action = 'create';
        return view('backend.shows.create', compact('show', 'interpretes', 'provincias', 'action'));
    }


    public function store(ShowRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['destacado'] = $request->has('destacado');
        $data['slug'] = $data['slug'] ?? Str::slug($data['show'] . '-' . now()->timestamp);

        if ($request->hasFile('imagen_destacada')) {
            $filename = time() . '_' . $request->file('imagen_destacada')->getClientOriginalName();
            $path = $request->file('imagen_destacada')->storeAs('shows', $filename, 'public');
            $data['imagen_destacada'] = $path;
        }

        $data['estado'] = Auth::user()->hasAnyRole(['prensa', 'administrador']) ? 1 : 0;

        Show::create($data);

        return redirect()->route('shows.index')->with('success', 'Show creado correctamente.');
    }


    public function edit(Show $show)
    {
        // Obtener los intérpretes activos ordenados y con los campos necesarios
        $interpretes = Interprete::active()->get();
        $provincias = Provincia::all();

        $action = 'edit';
        return view('backend.shows.edit', compact('show', 'provincias', 'interpretes', 'action'));
    }


    public function update(ShowRequest $request, Show $show)
    {
        $this->authorize('update', $show);

        $data = $request->validated();
        $data['destacado'] = $request->has('destacado');
        $data['slug'] = $data['slug'] ?? Str::slug($data['show'] . '-' . now()->timestamp);

        if ($request->hasFile('imagen_destacada')) {
            $filename = time() . '_' . $request->file('imagen_destacada')->getClientOriginalName();
            $path = $request->file('imagen_destacada')->storeAs('shows', $filename, 'public');
            $data['imagen_destacada'] = $path;
        }

        $show->update($data);

        return redirect()->route('shows.index')->with('success', 'Show actualizado correctamente.');
    }


    public function destroy(Show $show)
    {
        $this->authorize('delete', $show);
        $show->delete();

        Alert::success('Show eliminada', 'El show ha sido eliminado con éxito.');
        return redirect()->route('backend.shows.index');
    }


    private function sendNotification(Show $show)
    {
        $details = [
            'title' => 'Se ha agregado un Show en el portal',
            'show' => $show->show,
            'interprete' => $show->interprete->nombre,
            'user' => $show->user->name,
        ];

        Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\ShowCreated($details));
    }
}
