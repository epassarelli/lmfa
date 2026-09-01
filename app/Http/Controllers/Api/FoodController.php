<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFoodRequest;
use App\Http\Requests\Api\UpdateFoodRequest;
use App\Models\Comida;
use App\Services\ImageSourceResolver;
use App\Services\ImageUploadService;
use App\Support\ApiImageInput;
use App\Support\ImageSourceMetadata;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FoodController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageService,
        protected ImageSourceResolver $imageResolver
    ) {
    }

    public function index(Request $request)
    {
        $query = Comida::query()->with('images');

        if ($request->boolean('active_only')) {
            $query->where('estado', 1);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($inner) use ($search) {
                $inner->where('titulo', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.Str::slug($search).'%');
            });
        }

        return response()->json($query->latest()->paginate(50));
    }

    public function store(StoreFoodRequest $request)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        unset($payload['featured_image_path']);

        $food = DB::transaction(function () use ($payload, $image) {
            $food = Comida::create($this->normalizePayload($payload));
            $this->processImage($food, $image, false);
            $this->syncImageAlt($food, $payload);

            return $food;
        });

        return response()->json($food->fresh('images'), 201);
    }

    public function show(Comida $food)
    {
        return response()->json($food->load('images'));
    }

    public function update(UpdateFoodRequest $request, Comida $food)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        unset($payload['featured_image_path']);

        DB::transaction(function () use ($food, $payload, $image) {
            $food->update($this->normalizePayload($payload, $food));
            $this->processImage($food, $image, true);
            $this->syncImageAlt($food, $payload);
        });

        return response()->json($food->fresh('images'));
    }

    public function destroy(Comida $food)
    {
        $food->delete();

        return response()->json(null, 204);
    }

    private function normalizePayload(array $payload, ?Comida $food = null): array
    {
        $title = $payload['titulo'] ?? $food?->titulo;

        if (! $food) {
            $payload['user_id'] = auth()->id();
        } else {
            unset($payload['user_id'], $payload['visitas']);
        }

        if (! $food && ! array_key_exists('estado', $payload)) {
            $payload['estado'] = 0;
        }

        if (! $food && ! array_key_exists('visitas', $payload)) {
            $payload['visitas'] = 0;
        }

        if (! array_key_exists('slug', $payload) && filled($title) && (! $food || blank($food->slug))) {
            $payload['slug'] = Str::slug($title);
        }

        if (array_key_exists('receta', $payload)) {
            $payload['receta'] = RichTextHeadingSanitizer::normalize($payload['receta']);
        }

        $effectiveState = (int) ($payload['estado'] ?? $food?->estado ?? 0);
        $effectivePublishAt = $payload['publicar'] ?? $food?->publicar;

        if ($effectiveState === 1 && empty($effectivePublishAt)) {
            $payload['publicar'] = now();
        }

        return $payload;
    }

    private function processImage(Comida $food, mixed $image, bool $replace): void
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

            $this->imageService->process(
                $resolved,
                $food,
                'recipe',
                'comidas',
                $replace,
                $food->slug,
                array_merge(
                    ImageSourceMetadata::from($image),
                    ['alt' => $food->image_alt ?: $food->titulo]
                )
            );
        } finally {
            $this->imageResolver->cleanupTemporary($resolved);
        }
    }

    private function syncImageAlt(Comida $food, array $payload): void
    {
        if (! array_key_exists('image_alt', $payload)) {
            return;
        }

        $image = $food->images()->orderBy('sort_order')->first();

        if ($image) {
            $image->update([
                'alt' => $food->image_alt ?: $food->titulo,
            ]);
        }
    }
}
