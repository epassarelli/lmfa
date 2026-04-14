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
use App\Services\ImageUploadService;

use Illuminate\Http\Request;

class ShowController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->middleware('auth');
        $this->imageService = $imageService;
        $this->authorizeResource(Show::class, 'show');
    }


    public function index()
    {
        $user = Auth::user();
        $shows = Show::query()
            ->when($user->hasRole(['colaborador', 'prensa']), function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->where('start_at', '>=', now())
            ->with(['user', 'interpretes', 'images'])
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
        $data['created_by'] = Auth::id();
        $data['slug'] = $data['slug'] ?? Str::slug($data['show'] . '-' . now()->timestamp);

        // Extraer interprete_id para sincronizar después (es many-to-many)
        $interpreteId = $data['interprete_id'] ?? null;
        unset($data['interprete_id']);

        // Si no viene estado del formulario, asignarlo según el rol del usuario
        if (!isset($data['estado'])) {
            $data['estado'] = Auth::user()->hasAnyRole(['prensa', 'administrador']) ? 1 : 0;
        }

        $show = Show::create($data);

        // Sincronizar intérprete principal en la tabla pivote
        if ($interpreteId) {
            $show->interpretes()->sync([$interpreteId]);
        }

        if ($request->hasFile('imagen_destacada')) {
            $this->imageService->process(
                $request->file('imagen_destacada'),
                $show,
                'news_full',
                'shows'
            );
        }

        return redirect()->route('backend.shows.index')->with('success', 'Show creado correctamente.');
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

        // Verificar que el show existe y tiene ID
        if (!$show->exists || !$show->id) {
            return redirect()->route('backend.shows.index')->with('error', 'Show no encontrado.');
        }

        $data = $request->validated();

        // Extraer interprete_id para sincronizar después (es many-to-many)
        $interpreteId = $data['interprete_id'] ?? null;
        unset($data['interprete_id']);

        // Preservar slug si viene y no está vacío, sino mantener el actual
        if (isset($data['slug']) && empty($data['slug'])) {
            unset($data['slug']);
        } elseif (!isset($data['slug'])) {
            unset($data['slug']);
        }

        // Remover campos que no existen en la BD de events
        unset($data['precio_entrada'], $data['link_entradas'], $data['lat'], $data['lng']);

        // Manejar imagen destacada si viene
        if ($request->hasFile('imagen_destacada')) {
            $this->imageService->process(
                $request->file('imagen_destacada'),
                $show,
                'news_full',
                'shows',
                true
            );
        }

        // Manejar destacado
        if ($request->has('destacado')) {
            $data['destacado'] = (bool) $request->input('destacado', 0);
        } else {
            unset($data['destacado']);
        }

        // Actualizar el registro existente
        $show->fill($data);
        $show->save();

        // Sincronizar intérprete en la tabla pivote
        if ($interpreteId) {
            $show->interpretes()->sync([$interpreteId]);
        }

        return redirect()->route('backend.shows.index')->with('success', 'Show actualizado correctamente.');
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
