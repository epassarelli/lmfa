<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComidaRequest;
use App\Models\Comida;
use App\Models\Mes;
use App\Models\Provincia;
use App\Services\ImageUploadService;
use App\Support\BackendListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class ComidaController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->middleware('auth');
        $this->imageService = $imageService;
        $this->authorizeResource(Comida::class, 'comida');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedSorts = ['titulo', 'visitas', 'estado', 'id'];
        [$sort, $direction] = BackendListing::resolveSort($request, $allowedSorts, 'titulo', 'asc');
        $search = trim($request->string('search')->toString());

        $comidas = Comida::query()
            ->when($user->hasRole('colaborador') || $user->hasRole('prensa'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['user', 'images'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($foodQuery) use ($search) {
                    $foodQuery
                        ->where('titulo', 'like', "%{$search}%")
                        ->orWhere('receta', 'like', "%{$search}%")
                        ->orWhere('visitas', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

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
        $payload = $request->validated();
        $comida = new Comida($payload);
        $comida->slug = filled($payload['slug'] ?? null)
            ? Str::slug($payload['slug'])
            : Str::slug($comida->titulo);
        $comida->user_id = Auth::id();
        if (Auth::user()->hasRole('administrador')) {
            if (! array_key_exists('estado', $payload)) {
                $comida->estado = 1;
            }
        } else {
            $comida->estado = 0;
        }
        $comida->save();

        if ($request->hasFile('foto')) {
            $this->imageService->process(
                $request->file('foto'),
                $comida,
                'recipe',
                'comidas',
                false,
                $comida->slug,
                ['alt' => $comida->image_alt ?: $comida->titulo]
            );
        }

        if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
            $this->sendNotification($comida);
        }

        Alert::success('Comida creada', 'La comida ha sido creada con exito.');

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
        $payload = $request->validated();
        $comida->fill($payload);
        $comida->slug = filled($payload['slug'] ?? null)
            ? Str::slug($payload['slug'])
            : Str::slug($comida->titulo);
        if (Auth::user()->hasRole('administrador')) {
            if (! array_key_exists('estado', $payload)) {
                $comida->estado = 1;
            }
        } else {
            $comida->estado = 0;
        }
        $comida->save();

        if ($request->hasFile('foto')) {
            $this->imageService->process(
                $request->file('foto'),
                $comida,
                'recipe',
                'comidas',
                true,
                $comida->slug,
                ['alt' => $comida->image_alt ?: $comida->titulo]
            );
        }

        if (array_key_exists('image_alt', $payload)) {
            $image = $comida->images()->orderBy('sort_order')->first();
            if ($image) {
                $image->update(['alt' => $comida->image_alt ?: $comida->titulo]);
            }
        }

        Alert::success('Comida actualizada', 'La comida ha sido actualizada con exito.');

        return redirect()->route('backend.comidas.index');
    }

    public function destroy(Comida $comida)
    {
        $this->authorize('delete', $comida);
        $comida->delete();

        Alert::success('Comida eliminada', 'La comida ha sido eliminada con exito.');

        return redirect()->route('backend.comidas.index');
    }

    private function sendNotification(Comida $comida)
    {
        $details = [
            'title' => 'Se ha agregado una comida en el portal',
            'comida' => $comida->titulo,
            'user' => $comida->user->name,
        ];

        Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\ComidaCreated($details));
    }
}
