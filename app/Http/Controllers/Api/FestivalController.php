<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFestivalRequest;
use App\Http\Requests\Api\UpdateFestivalRequest;
use App\Models\Festival;
use App\Services\ImageSourceResolver;
use App\Services\ImageUploadService;
use App\Support\ApiImageInput;
use App\Support\ImageSourceMetadata;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FestivalController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageService,
        protected ImageSourceResolver $imageResolver
    ) {
    }
    public function index(Request $request)
    {
        $query = Festival::query()->with(['provincia', 'mes', 'locality']);

        if ($request->has('province_id')) {
            $query->where('province_id', $request->query('province_id'));
        }

        if ($request->has('mes_id')) {
            $query->where('mes_id', $request->query('mes_id'));
        }

        if ($request->boolean('published_only', true)) {
            $query->publishedVisible();
        }

        return response()->json($query->latest('published_at')->paginate(15));
    }

    public function store(StoreFestivalRequest $request)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');

        $festival = DB::transaction(function () use ($payload, $image) {
            $festival = Festival::create($this->normalizePayload($payload));
            $this->syncRelations($festival, $payload, true);
            $this->processImage($festival, $image, false);

            return $festival;
        });

        return response()->json($this->freshWithRelations($festival), 201);
    }

    public function show(Festival $festival)
    {
        return response()->json($festival->load([
            'provincia',
            'mes',
            'locality',
            'images',
            'noticias',
            'events',
            'interpretes',
            'knowledgeArticles',
        ]));
    }

    public function update(UpdateFestivalRequest $request, Festival $festival)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');

        DB::transaction(function () use ($festival, $payload, $image) {
            $festival->update($this->normalizePayload($payload, $festival));
            $this->syncRelations($festival, $payload);
            $this->processImage($festival, $image, true);
        });

        return response()->json($this->freshWithRelations($festival));
    }

    public function destroy(Festival $festival)
    {
        $festival->delete();

        return response()->json(null, 204);
    }

    private function processImage(Festival $festival, mixed $image, bool $replace): void
    {
        if (! $image) {
            return;
        }

        $resolved = null;

        try {
            $resolved = $this->imageResolver->resolve($image);

            if (! $resolved instanceof \Illuminate\Http\UploadedFile) {
                return;
            }

            $media = $this->imageService->process(
                $resolved,
                $festival,
                'festival',
                'festivales',
                $replace,
                $festival->slug,
                array_merge(
                    ImageSourceMetadata::from($image),
                    ['alt' => $festival->image_alt ?: $festival->title]
                )
            );

            $festival->forceFill([
                'featured_image_id' => $media->id,
                'featured_image_path' => $media->path,
            ])->save();
        } finally {
            $this->imageResolver->cleanupTemporary($resolved);
        }
    }

    private function normalizePayload(array $validated, ?Festival $festival = null): array
    {
        $title = $validated['title'] ?? $festival?->title;
        $body = $validated['body'] ?? $festival?->body;

        if (! array_key_exists('slug', $validated) && filled($title) && (! $festival || blank($festival->slug))) {
            $validated['slug'] = Str::slug($title);
        }

        $validated['title'] = $title;
        $validated['body'] = RichTextHeadingSanitizer::normalize($body);

        if (! array_key_exists('published_at', $validated) && $festival?->published_at) {
            $validated['published_at'] = $festival->published_at;
        }

        if (! array_key_exists('status', $validated) && $festival?->status) {
            $validated['status'] = $festival->status;
        }

        $effectiveStatus = $validated['status'] ?? $festival?->status;
        $effectivePublishedAt = $validated['published_at'] ?? $festival?->published_at;

        if ($effectiveStatus === 'published' && empty($effectivePublishedAt)) {
            $validated['published_at'] = now();
        }

        return $validated;
    }

    private function syncRelations(Festival $festival, array $payload, bool $creating = false): void
    {
        $relations = [
            'news_ids' => 'noticias',
            'event_ids' => 'events',
            'interprete_ids' => 'interpretes',
            'knowledge_article_ids' => 'knowledgeArticles',
        ];

        foreach ($relations as $field => $relation) {
            if ($creating || array_key_exists($field, $payload)) {
                $festival->{$relation}()->sync($payload[$field] ?? []);
            }
        }
    }

    private function freshWithRelations(Festival $festival): Festival
    {
        return $festival->fresh([
            'provincia',
            'mes',
            'locality',
            'images',
            'noticias',
            'events',
            'interpretes',
            'knowledgeArticles',
        ]);
    }
}
