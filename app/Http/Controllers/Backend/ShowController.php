<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Show;
use App\Models\Interprete;
use App\Http\Requests\ShowRequest;
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
            ->when($user->hasRole('colaborador'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($user->hasRole('prensa'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('user', 'interprete')
            ->get();

        return view('backend.shows.index', compact('shows'));
    }


    public function create()
    {
        // Obtener los intérpretes activos ordenados y con los campos necesarios
        $interpretes = Interprete::active()->get();

        $action = 'create';
        return view('backend.shows.create', compact('interpretes', 'action'));
    }


    public function store(ShowRequest $request)
    {
        $show = new Show($request->validated());
        $show->slug = Str::slug($show->show);
        $show->user_id = Auth::id();
        // $show->estado = Auth::user()->hasRole('prensa') ? 1 : 0;
        $show->estado = Auth::user()->hasAnyRole(['prensa', 'administrador']) ? 1 : 0;


        if ($request->hasFile('foto')) {
            $filePath = $request->file('foto')->store('shows', 'public');
            $show->foto = $filePath;
        }

        $show->save();

        if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
            $this->sendNotification($show);
        }

        Alert::success('Show creado', 'El show ha sido creado con éxito.');
        return redirect()->route('backend.shows.index');
    }


    public function edit(Show $show)
    {
        // Obtener los intérpretes activos ordenados y con los campos necesarios
        $interpretes = Interprete::active()->get();

        $action = 'edit';
        return view('backend.shows.edit', compact('show', 'interpretes', 'action'));
    }


    public function update(ShowRequest $request, Show $show)
    {
        $show->fill($request->validated());

        $show->slug = Str::slug($show->show);
        $show->user_id = Auth::id();
        // Si es Colaborador pasa a Inactivo
        $show->estado = Auth::user()->hasAnyRole(['prensa', 'administrador']) ? 1 : 0;

        if ($request->hasFile('foto')) {
            $filePath = $request->file('foto')->store('shows', 'public');
            $show->foto = $filePath;
        }

        $show->save();

        Alert::success('Show actualizado', 'El show ha sido actualizado con éxito.');
        return redirect()->route('backend.shows.index');
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
