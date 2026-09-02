<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlbumRequest;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Interprete;
use App\Models\User;
use App\Services\ImageUploadService;
use App\Support\BackendListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class AlbumController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->middleware('auth');
        $this->imageService = $imageService;
        $this->authorizeResource(Album::class, 'album');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedSorts = ['id', 'anio', 'album', 'visitas', 'spotify', 'canciones_count'];
        [$sort, $direction] = BackendListing::resolveSort($request, $allowedSorts, 'id');
        $search = trim($request->string('search')->toString());

        $albums = Album::query()
            ->when($user->hasRole('colaborador') || $user->hasRole('prensa'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('interprete', 'user', 'images')
            ->withCount('canciones')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($albumQuery) use ($search) {
                    $albumQuery
                        ->where('album', 'like', "%{$search}%")
                        ->orWhere('anio', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhereHas('interprete', function ($interpreteQuery) use ($search) {
                            $interpreteQuery->where('interprete', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('backend.albunes.index', compact('albums'));
    }

    public function create()
    {
        $action = 'create';
        $interpretes = Interprete::active()->get();

        return view('backend.albunes.create', compact('interpretes', 'action'));
    }

    public function store(AlbumRequest $request)
    {
        $payload = $request->validated();
        $album = new Album($payload);

        $album->slug = filled($payload['slug'] ?? null)
            ? Str::slug($payload['slug'])
            : Str::slug($album->album);
        $album->user_id = Auth::id();
        $album->estado = Auth::user()->hasRole('administrador') ? 1 : 0;
        $album->visitas = 0;
        $album->save();

        if ($request->hasFile('foto')) {
            $this->imageService->process(
                $request->file('foto'),
                $album,
                'album',
                'albunes',
                false,
                $album->slug,
                ['alt' => $album->image_alt ?: $album->album]
            );
        }

        if (Auth::user()->hasRole(['prensa', 'colaborador'])) {
            $this->sendNotification($album);
        }

        Alert::success('Album creado', 'El album ha sido creado con exito.');

        return redirect()->route('backend.discos.index');
    }

    public function edit(Album $album)
    {
        $action = 'edit';
        $interpretes = Interprete::active()->get();
        $interpreteId = $album->interprete_id;

        $album_canciones_ids = $album->canciones->pluck('id')->toArray();
        $canciones = Cancion::where('interprete_id', $interpreteId)
            ->whereNotIn('id', $album_canciones_ids)
            ->orderBy('cancion', 'asc')
            ->get();
        $album_canciones = $album->canciones;

        return view('backend.albunes.edit', compact('album', 'canciones', 'album_canciones', 'interpretes', 'action'));
    }

    public function update(AlbumRequest $request, Album $album)
    {
        $payload = $request->validated();
        $album->fill($payload);
        $album->slug = filled($payload['slug'] ?? null)
            ? Str::slug($payload['slug'])
            : Str::slug($album->album);
        $album->user_id = Auth::id();
        $album->estado = Auth::user()->hasRole('administrador') ? 1 : 0;

        if ($request->hasFile('foto')) {
            $this->imageService->process(
                $request->file('foto'),
                $album,
                'album',
                'albunes',
                true,
                $album->slug,
                ['alt' => $album->image_alt ?: $album->album]
            );
        }

        $canciones = $request->input('canciones', []);
        $ordenes = $request->input('ordenes', []);
        $syncData = [];

        foreach ($canciones as $index => $cancionId) {
            $syncData[$cancionId] = ['orden' => $ordenes[$index]];
        }

        $album->canciones()->sync($syncData);
        $album->save();

        if (array_key_exists('image_alt', $payload)) {
            $image = $album->images()->orderBy('sort_order')->first();
            if ($image) {
                $image->update(['alt' => $album->image_alt ?: $album->album]);
            }
        }

        return redirect()->route('backend.discos.index')->with('success', 'Album actualizado exitosamente.');
    }

    public function destroy(Album $album)
    {
        if (! Auth::user()->isAdmin()) {
            return redirect()->route('backend.discos.index')
                ->with('error', 'No tienes permiso para eliminar este disco.');
        }

        $album->delete();

        Alert::success('Album eliminado', 'El album se ha eliminado con exito.');

        return redirect()->route('backend.discos.index');
    }

    private function sendNotification(Album $album)
    {
        try {
            $details = [
                'title' => 'Se ha agregado un album en el portal',
                'album' => $album->album,
                'foto' => $album->foto,
                'anio' => $album->anio,
                'interprete' => $album->interprete?->interprete ?? '-',
                'user' => $album->user?->name ?? 'Invitado',
            ];

            Mail::to('info@mifolkloreargentino.com')->send(new \App\Mail\AlbumCreated($details));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de Album: '.$e->getMessage());
        }
    }
}
