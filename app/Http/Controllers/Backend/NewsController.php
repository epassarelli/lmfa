<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoticiaRequest;
use App\Models\Categoria;
use App\Models\Interprete;
use App\Models\News;
use App\Services\NewsService;
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

    public function index()
    {
        $this->authorize('viewAny', News::class);

        $query = News::with(['interpretes:id,interprete', 'user:id,name', 'categoria:id,nombre', 'images'])
            ->orderBy('published_at', 'desc');

        // Si no es admin, solo ve lo suyo
        if (!auth()->user()->isAdmin()) {
            $query->where('created_by', auth()->id());
        }

        $news = $query->get();

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
