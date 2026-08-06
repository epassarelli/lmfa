<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\KnowledgeArticleRequest;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Provincia;
use App\Services\KnowledgeArticleService;
use App\Support\CanonicalUrl;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class KnowledgeArticleController extends Controller
{
    public function __construct(protected KnowledgeArticleService $service)
    {
        $this->middleware('auth');
        $this->authorizeResource(KnowledgeArticle::class, 'knowledge_article');
    }

    public function index(Request $request)
    {
        $query = KnowledgeArticle::query()
            ->with(['category', 'author'])
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('knowledge_category_id')) {
            $query->where('knowledge_category_id', $request->integer('knowledge_category_id'));
        }

        if ($request->filled('editorial_status')) {
            $query->where('editorial_status', $request->string('editorial_status'));
        }

        if ($request->filled('published_from')) {
            $query->whereDate('published_at', '>=', $request->input('published_from'));
        }

        if ($request->filled('published_to')) {
            $query->whereDate('published_at', '<=', $request->input('published_to'));
        }

        $articles = $query->paginate(20)->withQueryString();
        $categories = KnowledgeCategory::active()->get();

        return view('backend.knowledge_articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $article = new KnowledgeArticle([
            'editorial_status' => auth()->user()->canPublish() ? 'published' : 'draft',
            'published_at' => now(),
            'last_verified_at' => now(),
        ]);

        return view('backend.knowledge_articles.create', $this->formData($article));
    }

    public function store(KnowledgeArticleRequest $request)
    {
        $payload = $request->validated();
        $article = $this->service->createArticle($payload, $request->file('image'));

        Alert::success('Artículo creado', 'La entrada de la enciclopedia fue creada con éxito.');

        return redirect()->route('backend.knowledge-articles.edit', $article);
    }

    public function show(KnowledgeArticle $knowledge_article)
    {
        return view('backend.knowledge_articles.show', [
            'article' => $knowledge_article->load($this->service->defaultRelations()),
        ]);
    }

    public function edit(KnowledgeArticle $knowledge_article)
    {
        return view('backend.knowledge_articles.edit', $this->formData($knowledge_article));
    }

    public function update(KnowledgeArticleRequest $request, KnowledgeArticle $knowledge_article)
    {
        $article = $this->service->updateArticle($knowledge_article, $request->validated(), $request->file('image'));

        Alert::success('Artículo actualizado', 'La entrada de la enciclopedia fue actualizada con éxito.');

        return redirect()->route('backend.knowledge-articles.edit', $article);
    }

    public function destroy(KnowledgeArticle $knowledge_article)
    {
        $this->service->archive($knowledge_article, auth()->user());

        Alert::success('Artículo archivado', 'La entrada fue archivada correctamente.');

        return redirect()->route('backend.knowledge-articles.index');
    }

    public function preview(KnowledgeArticle $knowledge_article)
    {
        $article = $knowledge_article->load([
            'category',
            'author',
            'images',
            'interpretes',
            'canciones.interprete',
            'albums.interprete',
            'festivales.provincia',
            'events.provincia',
            'provincias',
            'relatedArticles.category',
        ]);

        $metaTitle = $article->seo_title ?: $article->title.' | Enciclopedia del folklore argentino';
        $metaDescription = $article->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($article->excerpt ?: $article->body), 160);
        $canonical = CanonicalUrl::normalize(route('enciclopedia.show', [
            'categorySlug' => $article->category->slug,
            'articleSlug' => $article->slug,
        ]));
        $breadcrumbs = [
            ['label' => 'Enciclopedia', 'url' => route('enciclopedia.index')],
            ['label' => $article->category->name],
            ['label' => $article->title],
        ];

        return view('frontend.knowledge.show', compact(
            'article',
            'metaTitle',
            'metaDescription',
            'canonical',
            'breadcrumbs'
        ));
    }

    public function publish(KnowledgeArticle $knowledge_article)
    {
        $this->authorize('update', $knowledge_article);
        $this->service->publish($knowledge_article, auth()->user());

        Alert::success('Artículo publicado', 'La entrada ya está visible en la enciclopedia.');

        return back();
    }

    public function unpublish(KnowledgeArticle $knowledge_article)
    {
        $this->authorize('update', $knowledge_article);
        $this->service->unpublish($knowledge_article, auth()->user());

        Alert::success('Artículo despublicado', 'La entrada volvió a borrador.');

        return back();
    }

    protected function formData(KnowledgeArticle $article): array
    {
        $article->loadMissing(['interpretes', 'canciones', 'albums', 'festivales', 'events', 'provincias', 'relatedArticles']);

        return [
            'article' => $article,
            'categories' => KnowledgeCategory::active()->get(),
            'interpretes' => Interprete::active()->get(),
            'canciones' => Cancion::orderBy('cancion')->limit(200)->get(['id', 'cancion', 'slug']),
            'albums' => Album::orderBy('album')->limit(200)->get(['id', 'album', 'slug']),
            'festivales' => Festival::orderBy('titulo')->limit(200)->get(['id', 'titulo', 'slug']),
            'events' => Event::orderByDesc('start_at')->limit(200)->get(['id', 'title', 'slug']),
            'provincias' => Provincia::orderBy('nombre')->get(),
            'relatedArticles' => KnowledgeArticle::query()
                ->when($article->exists, fn ($query) => $query->where('id', '<>', $article->id))
                ->orderByDesc('created_at')
                ->limit(200)
                ->get(['id', 'title']),
        ];
    }
}
