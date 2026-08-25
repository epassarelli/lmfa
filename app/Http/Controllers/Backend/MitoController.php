<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\MitoRequest;
use App\Models\Mes;
use App\Models\Mito;
use App\Models\Provincia;
use App\Services\ImageUploadService;
use App\Support\BackendListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MitoController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->middleware('auth');
        $this->imageService = $imageService;
        $this->authorizeResource(Mito::class, 'mito');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedSorts = ['titulo', 'visitas', 'estado', 'id'];
        [$sort, $direction] = BackendListing::resolveSort($request, $allowedSorts, 'titulo', 'asc');
        $search = trim($request->string('search')->toString());

        $mitos = Mito::query()
            ->when($user->hasRole('colaborador') || $user->hasRole('prensa'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['user', 'images'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($mythQuery) use ($search) {
                    $mythQuery
                        ->where('titulo', 'like', "%{$search}%")
                        ->orWhere('mito', 'like', "%{$search}%")
                        ->orWhere('visitas', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('backend.mitos.index', compact('mitos'));
    }

    public function create()
    {
        $provincias = Provincia::all();
        $meses = Mes::all();

        return view('backend.mitos.create', compact('provincias', 'meses'));
    }

    public function store(MitoRequest $request)
    {
        $mito = new Mito($request->validated());
        $mito->slug = Str::slug($mito->titulo);
        $mito->user_id = Auth::id();
        $mito->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
        $mito->save();

        if ($request->hasFile('foto')) {
            $this->imageService->process(
                $request->file('foto'),
                $mito,
                'news_full',
                'mitos'
            );
        }

        if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
            $this->sendNotification($mito);
        }

        Alert::success('Mito creado', 'El mito ha sido creado con exito.');

        return redirect()->route('backend.mitos.index');
    }

    public function edit(Mito $mito)
    {
        $provincias = Provincia::all();
        $meses = Mes::all();

        return view('backend.mitos.edit', compact('mito', 'provincias', 'meses'));
    }

    public function update(MitoRequest $request, Mito $mito)
    {
        $mito->fill($request->validated());
        $mito->slug = Str::slug($mito->titulo);
        $mito->user_id = Auth::id();
        $mito->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
        $mito->save();

        if ($request->hasFile('foto')) {
            $this->imageService->process(
                $request->file('foto'),
                $mito,
                'news_full',
                'mitos',
                true
            );
        }

        Alert::success('Mito actualizado', 'El mito ha sido actualizado con exito.');

        return redirect()->route('backend.mitos.index');
    }

    public function destroy(Mito $mito)
    {
        $this->authorize('delete', $mito);
        $mito->delete();

        Alert::success('Mito eliminado', 'El mito ha sido eliminado con exito.');

        return redirect()->route('backend.mitos.index');
    }

    private function sendNotification(Mito $mito)
    {
        $details = [
            'title' => 'Se ha agregado un mito en el portal',
            'mito' => $mito->titulo,
            'user' => $mito->user->name,
        ];

        Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\MitoCreated($details));
    }
}
