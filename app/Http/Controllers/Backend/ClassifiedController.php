<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassifiedRequest;
use App\Http\Requests\UpdateClassifiedRequest;
use App\Models\Classified;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Services\ImageUploadService;

class ClassifiedController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->middleware('auth');
        $this->imageService = $imageService;
    }

    public function index()
    {
        $pendientes  = Classified::with(['category', 'user'])->where('estado', 'pendiente')->latest()->get();
        $activos     = Classified::with(['category', 'user'])->where('estado', 'activo')->latest()->get();
        $rechazados  = Classified::with(['category', 'user'])->where('estado', 'rechazado')->latest()->take(20)->get();

        return view('backend.classifieds.index', compact('pendientes', 'activos', 'rechazados'));
    }

    public function show(Classified $classified)
    {
        $classified->load(['category', 'tags', 'images', 'user']);
        return view('backend.classifieds.show', compact('classified'));
    }

    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('backend.classifieds.create', compact('categories', 'tags'));
    }

    public function store(StoreClassifiedRequest $request)
    {
        $data = $request->validated();
        $data['slug']   = \Illuminate\Support\Str::slug($data['title']) . '-' . \Illuminate\Support\Str::random(4);
        $data['estado'] = 'activo';
        $data['is_active'] = true;

        $classified = Classified::create($data);

        if ($request->has('tags')) {
            $classified->tags()->sync($request->tags);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $this->imageService->process($image, $classified, 'news_full', 'classifieds', false, $classified->slug);
            }
        }

        return redirect()->route('backend.classifieds.index')->with('success', 'Aviso creado y publicado.');
    }

    public function edit(Classified $classified)
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('backend.classifieds.edit', compact('classified', 'categories', 'tags'));
    }

    public function update(UpdateClassifiedRequest $request, Classified $classified)
    {
        $classified->update($request->validated());

        if ($request->has('tags')) {
            $classified->tags()->sync($request->tags);
        }

        if ($request->hasFile('images')) {
            $classified->images()->delete();
            foreach ($request->file('images') as $image) {
                $this->imageService->process($image, $classified, 'news_full', 'classifieds', false, $classified->slug);
            }
        }

        return redirect()->route('backend.classifieds.index')->with('success', 'Aviso actualizado.');
    }

    public function approve(Request $request, Classified $classified)
    {
        $classified->update([
            'estado'    => 'activo',
            'is_active' => true,
            'expiration_date' => now()->addDays(30)->toDateString(),
        ]);
        return redirect()->route('backend.classifieds.index')->with('success', 'Aviso aprobado y publicado por 30 días.');
    }

    public function reject(Request $request, Classified $classified)
    {
        $request->validate(['motivo' => 'nullable|string|max:500']);
        $classified->update([
            'estado'             => 'rechazado',
            'is_active'          => false,
            'moderator_comment'  => $request->motivo,
        ]);
        return redirect()->route('backend.classifieds.index')->with('success', 'Aviso rechazado.');
    }

    public function destroy(Classified $classified)
    {
        $classified->images()->delete();
        $classified->delete();
        return redirect()->route('backend.classifieds.index')->with('success', 'Aviso eliminado.');
    }
}
