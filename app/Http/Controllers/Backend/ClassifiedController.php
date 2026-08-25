<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassifiedRequest;
use App\Http\Requests\UpdateClassifiedRequest;
use App\Models\Category;
use App\Models\Classified;
use App\Models\Tag;
use App\Models\UserNotification;
use App\Services\ImageUploadService;
use App\Support\BackendListing;
use Illuminate\Http\Request;

class ClassifiedController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->middleware('auth');
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $allowedSorts = ['created_at', 'title', 'estado', 'expiration_date'];
        [$sort, $direction] = BackendListing::resolveSort($request, $allowedSorts, 'created_at');
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $allowedStatuses = ['pendiente', 'activo', 'rechazado'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $classifieds = Classified::query()
            ->with(['category', 'user'])
            ->when($status !== '', function ($query) use ($status) {
                $query->where('estado', $status);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($classifiedQuery) use ($search) {
                    $classifiedQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Classified::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->whereIn('estado', $allowedStatuses)
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('backend.classifieds.index', compact('classifieds', 'statusCounts', 'status'));
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
        $data['slug'] = \Illuminate\Support\Str::slug($data['title']).'-'.\Illuminate\Support\Str::random(4);
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
            'estado' => 'activo',
            'is_active' => true,
            'expiration_date' => now()->addDays(30)->toDateString(),
        ]);

        if ($classified->user_id) {
            UserNotification::notify(
                $classified->user_id,
                'classified.approved',
                'Tu aviso fue aprobado',
                "Tu aviso \"{$classified->title}\" esta publicado y visible por 30 dias."
            );
        }

        return redirect()->route('backend.classifieds.index')->with('success', 'Aviso aprobado y publicado por 30 dias.');
    }

    public function reject(Request $request, Classified $classified)
    {
        $request->validate(['motivo' => 'nullable|string|max:500']);

        $classified->update([
            'estado' => 'rechazado',
            'is_active' => false,
            'moderator_comment' => $request->motivo,
        ]);

        if ($classified->user_id) {
            UserNotification::notify(
                $classified->user_id,
                'classified.rejected',
                'Tu aviso fue rechazado',
                $request->motivo ? "Motivo: {$request->motivo}" : "Tu aviso \"{$classified->title}\" no pudo ser publicado."
            );
        }

        return redirect()->route('backend.classifieds.index')->with('success', 'Aviso rechazado.');
    }

    public function destroy(Classified $classified)
    {
        $classified->images()->delete();
        $classified->delete();

        return redirect()->route('backend.classifieds.index')->with('success', 'Aviso eliminado.');
    }
}
