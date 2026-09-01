<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAlbumRequest;
use App\Http\Requests\Api\UpdateAlbumRequest;
use App\Models\Album;
use App\Services\ImageSourceResolver;
use App\Services\ImageUploadService;
use App\Support\ApiImageInput;
use App\Support\ImageSourceMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageService,
        protected ImageSourceResolver $imageResolver
    ) {
    }

    public function index(Request $request)
    {
        $query = Album::query()->with(['interprete', 'images']);

        if ($request->has('interprete_id')) {
            $query->where('interprete_id', $request->query('interprete_id'));
        }

        if ($request->boolean('active_only')) {
            $query->where('estado', 1);
        }

        return response()->json($query->latest('anio')->paginate(50));
    }

    public function store(StoreAlbumRequest $request)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        unset($payload['featured_image_path']);

        $album = DB::transaction(function () use ($payload, $image) {
            $album = Album::create($this->normalizePayload($payload));
            $this->processImage($album, $image, false);
            $this->syncImageAlt($album, $payload);

            return $album;
        });

        return response()->json($this->freshWithRelations($album), 201);
    }

    public function show(Album $album)
    {
        return response()->json($this->freshWithRelations($album));
    }

    public function update(UpdateAlbumRequest $request, Album $album)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        unset($payload['featured_image_path']);

        DB::transaction(function () use ($album, $payload, $image) {
            $album->update($this->normalizePayload($payload, $album));
            $this->processImage($album, $image, true);
            $this->syncImageAlt($album, $payload);
        });

        return response()->json($this->freshWithRelations($album));
    }

    public function destroy(Album $album)
    {
        $album->delete();

        return response()->json(null, 204);
    }

    private function normalizePayload(array $payload, ?Album $album = null): array
    {
        $name = $payload['album'] ?? $album?->album;

        if (! $album && empty($payload['user_id'])) {
            $payload['user_id'] = auth()->id();
        }

        if (! $album && ! array_key_exists('estado', $payload)) {
            $payload['estado'] = false;
        }

        if (! $album && ! array_key_exists('visitas', $payload)) {
            $payload['visitas'] = 0;
        }

        if (! array_key_exists('slug', $payload) && filled($name) && (! $album || blank($album->slug))) {
            $payload['slug'] = Str::slug($name);
        }

        return $payload;
    }

    private function processImage(Album $album, mixed $image, bool $replace): void
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
                $album,
                'album',
                'albunes',
                $replace,
                $album->slug,
                array_merge(
                    ImageSourceMetadata::from($image),
                    ['alt' => $album->image_alt ?: $album->album]
                )
            );
        } finally {
            $this->imageResolver->cleanupTemporary($resolved);
        }
    }

    private function syncImageAlt(Album $album, array $payload): void
    {
        if (! array_key_exists('image_alt', $payload)) {
            return;
        }

        $image = $album->images()->orderBy('sort_order')->first();

        if ($image) {
            $image->update(['alt' => $album->image_alt ?: $album->album]);
        }
    }

    private function freshWithRelations(Album $album): Album
    {
        return $album->fresh(['interprete', 'images', 'canciones']);
    }
}
