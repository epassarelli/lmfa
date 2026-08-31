<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreKnowledgeArticleRequest;
use App\Http\Requests\Api\UpdateKnowledgeArticleRequest;
use App\Http\Resources\KnowledgeArticleResource;
use App\Http\Resources\KnowledgeCategoryResource;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Services\KnowledgeArticleService;
use App\Support\ApiImageInput;
use Illuminate\Http\Request;

class KnowledgeArticleController extends Controller
{
    public function __construct(protected KnowledgeArticleService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', KnowledgeArticle::class);

        $query = KnowledgeArticle::query()
            ->with(['category', 'author', 'reviewer'])
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
            $query->whereDate('published_at', '>=', $request->date('published_from'));
        }

        if ($request->filled('published_to')) {
            $query->whereDate('published_at', '<=', $request->date('published_to'));
        }

        return KnowledgeArticleResource::collection(
            $query->paginate((int) $request->input('per_page', 15))
        );
    }

    public function store(StoreKnowledgeArticleRequest $request)
    {
        $this->authorize('create', KnowledgeArticle::class);

        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'image');

        $article = $this->service->createArticle($payload, $image);

        return (new KnowledgeArticleResource($article->load($this->service->defaultRelations())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(KnowledgeArticle $knowledge_article)
    {
        $this->authorize('view', $knowledge_article);

        return new KnowledgeArticleResource(
            $knowledge_article->load($this->service->defaultRelations())
        );
    }

    public function update(UpdateKnowledgeArticleRequest $request, KnowledgeArticle $knowledge_article)
    {
        $this->authorize('update', $knowledge_article);

        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'image');

        $article = $this->service->updateArticle($knowledge_article, $payload, $image);

        return new KnowledgeArticleResource($article);
    }

    public function destroy(KnowledgeArticle $knowledge_article)
    {
        $this->authorize('delete', $knowledge_article);
        $this->service->archive($knowledge_article, $requestUser = auth()->user());

        return response()->json(null, 204);
    }

    public function publish(KnowledgeArticle $knowledge_article)
    {
        $this->authorize('update', $knowledge_article);

        return new KnowledgeArticleResource(
            $this->service->publish($knowledge_article, auth()->user())
        );
    }

    public function unpublish(KnowledgeArticle $knowledge_article)
    {
        $this->authorize('update', $knowledge_article);

        return new KnowledgeArticleResource(
            $this->service->unpublish($knowledge_article, auth()->user())
        );
    }

    public function categories()
    {
        $this->authorize('viewAny', KnowledgeArticle::class);

        return KnowledgeCategoryResource::collection(
            KnowledgeCategory::active()->get()
        );
    }
}
