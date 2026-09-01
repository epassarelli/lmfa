<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMythRequest;
use App\Http\Requests\Api\UpdateMythRequest;
use App\Models\Mito;
use App\Services\ImageSourceResolver;
use App\Services\ImageUploadService;
use App\Support\ApiImageInput;
use App\Support\ImageSourceMetadata;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MythController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageService,
        protected ImageSourceResolver $imageResolver
    ) {
    }

    public function index(Request $request)
    {
        $query = Mito::query()->with('images');

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

        if ($request->filled('content_type')) {
            $query->where('content_type', $request->query('content_type'));
        }

        return response()->json($query->latest()->paginate(50));
    }

    public function store(StoreMythRequest $request)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        unset($payload['featured_image_path']);

        $myth = DB::transaction(function () use ($payload, $image) {
            $myth = Mito::create($this->normalizePayload($payload));
            $this->processImage($myth, $image, false);
            $this->syncImageAlt($myth, $payload);

            return $myth;
        });

        return response()->json($myth->fresh('images'), 201);
    }

    public function show(Mito $myth)
    {
        return response()->json($myth->load('images'));
    }

    public function update(UpdateMythRequest $request, Mito $myth)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        unset($payload['featured_image_path']);

        DB::transaction(function () use ($myth, $payload, $image) {
            $myth->update($this->normalizePayload($payload, $myth));
            $this->processImage($myth, $image, true);
            $this->syncImageAlt($myth, $payload);
        });

        return response()->json($myth->fresh('images'));
    }

    public function destroy(Mito $myth)
    {
        $myth->delete();

        return response()->json(null, 204);
    }

    private function normalizePayload(array $payload, ?Mito $myth = null): array
    {
        $title = $payload['titulo'] ?? $myth?->titulo;

        if (! $myth) {
            $payload['user_id'] = auth()->id();
        } else {
            unset($payload['user_id'], $payload['visitas']);
        }

        if (! $myth && ! array_key_exists('estado', $payload)) {
            $payload['estado'] = 0;
        }

        if (! $myth && ! array_key_exists('visitas', $payload)) {
            $payload['visitas'] = 0;
        }

        if (! array_key_exists('slug', $payload) && filled($title) && (! $myth || blank($myth->slug))) {
            $payload['slug'] = Str::slug($title);
        }

        if (array_key_exists('mito', $payload)) {
            $payload['mito'] = RichTextHeadingSanitizer::normalize($payload['mito']);
        }

        $effectiveState = (int) ($payload['estado'] ?? $myth?->estado ?? 0);
        $effectivePublishAt = $payload['publicar'] ?? $myth?->publicar;

        if ($effectiveState === 1 && empty($effectivePublishAt)) {
            $payload['publicar'] = now();
        }

        return $payload;
    }

    private function processImage(Mito $myth, mixed $image, bool $replace): void
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
                $myth,
                'myth',
                'mitos',
                $replace,
                $myth->slug,
                array_merge(
                    ImageSourceMetadata::from($image),
                    ['alt' => $myth->image_alt ?: $myth->titulo]
                )
            );
        } finally {
            $this->imageResolver->cleanupTemporary($resolved);
        }
    }

    private function syncImageAlt(Mito $myth, array $payload): void
    {
        if (! array_key_exists('image_alt', $payload)) {
            return;
        }

        $image = $myth->images()->orderBy('sort_order')->first();

        if ($image) {
            $image->update([
                'alt' => $myth->image_alt ?: $myth->titulo,
            ]);
        }
    }
}
