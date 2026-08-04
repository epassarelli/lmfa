<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreNewsRequest;
use App\Http\Requests\Api\UpdateNewsRequest;
use App\Models\News;
use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(protected NewsService $newsService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', News::class);

        $query = News::query()->with(['creator', 'categoria', 'interprete']);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->integer('categoria_id'));
        }

        if ($request->filled('editorial_status')) {
            $query->where('editorial_status', $request->string('editorial_status'));
        }

        if ($request->filled('published_from')) {
            $query->whereDate('published_at', '>=', $request->date('published_from'));
        }

        if ($request->filled('published_to')) {
            $query->whereDate('published_at', '<=', $request->date('published_to'));
        }

        $news = $query->latest('published_at')->latest('created_at')->paginate(15);

        return response()->json($news);
    }

    public function store(StoreNewsRequest $request)
    {
        $this->authorize('create', News::class);

        $news = $this->newsService->createNews(
            $request->validated(),
            $request->file('foto')
        );

        return response()->json($news, 201);
    }

    public function show(News $news)
    {
        $this->authorize('view', $news);

        return response()->json($news);
    }

    public function update(UpdateNewsRequest $request, News $news)
    {
        $this->authorize('update', $news);

        $news = $this->newsService->updateNews(
            $news,
            $request->validated(),
            $request->file('foto')
        );

        return response()->json($news);
    }

    public function destroy(News $news)
    {
        $this->authorize('delete', $news);

        $news->delete();

        return response()->json(null, 204);
    }
}
