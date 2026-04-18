<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Classified;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Services\ImageUploadService;

class ClassifiedsController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Listado público con filtros.
     */
    public function index(Request $request)
    {
        $query = Classified::with(['category', 'images'])
            ->where('estado', 'activo')
            ->where(function ($q) {
                $q->whereNull('expiration_date')
                  ->orWhere('expiration_date', '>=', now()->toDateString());
            });

        if ($request->filled('categoria')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->categoria));
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('provincia')) {
            $query->where('location', 'like', '%' . $request->provincia . '%');
        }

        $classifieds = $query->orderByDesc('is_featured')->latest()->paginate(15)->withQueryString();
        $categories  = Category::withCount(['classifieds' => fn($q) => $q->where('estado', 'activo')])->get();

        $selectedCategory = $request->categoria;

        return view('frontend.classifieds.index', compact('classifieds', 'categories', 'selectedCategory'));
    }

    /**
     * Detalle de un aviso.
     */
    public function show(Classified $classified)
    {
        abort_if($classified->estado !== 'activo', 404);
        $classified->load(['category', 'tags', 'images', 'user']);

        $related = Classified::where('category_id', $classified->category_id)
            ->where('id', '!=', $classified->id)
            ->where('estado', 'activo')
            ->with('images')
            ->latest()
            ->take(4)
            ->get();

        return view('frontend.classifieds.show', compact('classified', 'related'));
    }

    /**
     * Formulario de publicación (solo usuarios autenticado).
     */
    public function create()
    {
        $this->middleware('auth');
        $categories = Category::all();
        $tags       = Tag::all();
        return view('frontend.classifieds.create', compact('categories', 'tags'));
    }

    /**
     * Guardar nuevo aviso (estado: pendiente).
     */
    public function store(Request $request)
    {
        $this->middleware('auth');

        $validated = $request->validate([
            'category_id'      => 'required|exists:categories,id',
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'price'            => 'nullable|string|max:100',
            'location'         => 'required|string|max:255',
            'contact_info'     => 'required|string|max:255',
            'contact_whatsapp' => 'nullable|string|max:30',
            'images.*'         => 'nullable|image|max:4096',
            'tags'             => 'nullable|array',
            'tags.*'           => 'exists:tags,id',
        ]);

        $validated['user_id']         = Auth::id();
        $validated['slug']            = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['estado']          = 'pendiente';
        $validated['is_active']       = false;
        $validated['expiration_date'] = now()->addDays(30)->toDateString();

        $classified = Classified::create($validated);

        if (!empty($validated['tags'])) {
            $classified->tags()->sync($validated['tags']);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $this->imageService->process($img, $classified, 'news_full', 'classifieds', false, $classified->slug);
            }
        }

        return redirect()->route('classifieds.mis-avisos')
            ->with('success', '¡Tu aviso fue enviado! Será publicado una vez que lo revisemos.');
    }

    /**
     * Panel personal: mis avisos.
     */
    public function misAvisos()
    {
        $this->middleware('auth');
        $avisos = Classified::where('user_id', Auth::id())
            ->with(['category', 'images'])
            ->latest()
            ->paginate(10);
        return view('frontend.classifieds.mis-avisos', compact('avisos'));
    }

    /**
     * Renovar un aviso propio.
     */
    public function renovar(Classified $classified)
    {
        abort_if($classified->user_id !== Auth::id(), 403);
        $classified->update([
            'expiration_date' => now()->addDays(30)->toDateString(),
            'estado'          => 'pendiente',
            'is_active'       => false,
        ]);
        return back()->with('success', 'Tu aviso fue enviado para revisión nuevamente.');
    }
}
