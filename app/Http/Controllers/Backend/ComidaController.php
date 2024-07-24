<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Comida;
use App\Http\Requests\ComidaRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\Provincia;
use App\Models\Mes;

class ComidaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Comida::class, 'comida');
    }

    public function index()
    {
        $user = Auth::user();
        $comidas = Comida::query()
            ->when($user->hasRole('colaborador'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($user->hasRole('prensa'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('user')
            ->get();

        return view('backend.comidas.index', compact('comidas'));
    }

    public function create()
    {
        $provincias = Provincia::all();
        $meses = Mes::all();
        return view('backend.comidas.create', compact('provincias', 'meses'));
    }

    public function store(ComidaRequest $request)
    {
        $comida = new Comida($request->validated());
        $comida->slug = Str::slug($comida->titulo);
        $comida->user_id = Auth::id();
        $comida->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
        $comida->save();

        if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
            $this->sendNotification($comida);
        }

        Alert::success('Comida creada', 'La comida ha sido creada con éxito.');
        return redirect()->route('backend.comidas.index');
    }

    public function edit(Comida $comida)
    {
        $provincias = Provincia::all();
        $meses = Mes::all();
        return view('backend.comidas.edit', compact('comida', 'provincias', 'meses'));
    }

    public function update(ComidaRequest $request, Comida $comida)
    {
        $comida->fill($request->validated());
        $comida->slug = Str::slug($comida->titulo);
        $comida->estado = Auth::user()->hasRole('administrador') ? 1 : 0;

        $comida->save();

        Alert::success('Comida actualizada', 'La comida ha sido actualizada con éxito.');
        return redirect()->route('backend.comidas.index');
    }

    public function destroy(Comida $comida)
    {
        $this->authorize('delete', $comida);
        $comida->delete();

        Alert::success('Comida eliminada', 'La comida ha sido eliminada con éxito.');
        return redirect()->route('backend.comidas.index');
    }

    private function sendNotification(Comida $comida)
    {
        $details = [
            'title' => 'Se ha agregado un/a Comida en el portal',
            'comida' => $comida->titulo,
            'user' => $comida->user->name,
        ];

        Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\ComidaCreated($details));
    }
}
