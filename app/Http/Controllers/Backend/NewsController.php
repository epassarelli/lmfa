<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoticiaRequest;
use App\Models\Categoria;
use App\Models\Interprete;
use App\Models\News;
use App\Services\NewsService;
use App\Support\BackendListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->middleware('auth');
        $this->newsService = $newsService;
        $this->authorizeResource(News::class, 'news');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', News::class);

        [$sort, $direction] = BackendListing::resolveSort(
            $request,
            ['published_at', 'title', 'visitas', 'estado'],
            'published_at'
        );

        $query = News::with(['interpretes:id,interprete', 'user:id,name', 'categoria:id,nombre'])
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $builder->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('editorial_status', 'like', '%'.$search.'%')
                        ->orWhereHas('interpretes', fn ($relation) => $relation->where('interprete', 'like', '%'.$search.'%'))
                        ->orWhereHas('categoria', fn ($relation) => $relation->where('nombre', 'like', '%'.$search.'%'));
                });
            });

        // Si no es admin, solo ve lo suyo
        if (!auth()->user()->isAdmin()) {
            $query->where('created_by', auth()->id());
        }

        if ($sort === 'estado') {
            $query->orderBy('editorial_status', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $query->orderByDesc('created_at')->orderByDesc('id');

        $news = $query->paginate(25)->withQueryString();

        return view('backend.news.index', compact('news'));
    }

    public function create()
    {
        $this->authorize('create', News::class);

        $categorias = Categoria::all();
        $interpretes = Interprete::active()->get();
        $news = new News();

        return view('backend.news.create', compact('interpretes', 'categorias', 'news'));
    }

    public function store(NoticiaRequest $request)
    {
        $this->newsService->createNews(
            $request->validated(),
            $request->file('foto')
        );

        Alert::success('Noticia creada', 'La noticia ha sido creada con éxito.');
        return redirect()->route('backend.news.index');
    }

    public function show(News $news)
    {
        $this->authorize('view', $news);

        return view('backend.news.show', compact('news'));
    }

    public function edit(News $news)
    {
        $this->authorize('update', $news);

        $categorias = Categoria::all();
        $interpretes = Interprete::active()->get();

        return view('backend.news.edit', compact('news', 'interpretes', 'categorias'));
    }

    public function update(NoticiaRequest $request, News $news)
    {
        $this->authorize('update', $news);

        $this->newsService->updateNews(
            $news,
            $request->validated(),
            $request->file('foto')
        );

        Alert::success('Noticia actualizada', 'La noticia ha sido actualizada con éxito.');
        return redirect()->route('backend.news.index');
    }

    public function destroy(News $news)
    {
        $this->authorize('delete', $news);

        $news->delete();
        Alert::success('Noticia eliminada', 'La noticia ha sido eliminada con éxito.');
        return redirect()->route('backend.news.index');
    }
}
