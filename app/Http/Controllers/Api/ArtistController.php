<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreArtistRequest;
use App\Http\Requests\Api\UpdateArtistRequest;
use App\Models\Interprete;
use App\Services\ImageSourceResolver;
use App\Services\ImageUploadService;
use App\Support\ApiImageInput;
use App\Support\ImageSourceMetadata;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArtistController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageService,
        protected ImageSourceResolver $imageResolver
    ) {
    }

    public function index(Request $request)
    {
        $query = Interprete::query()->with('images');

        if ($request->boolean('active_only')) {
            $query->where('estado', 1);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));

            $query->where(function ($inner) use ($search) {
                $inner->where('interprete', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.Str::slug($search).'%');
            });
        }

        return response()->json($query->orderBy('interprete')->paginate(50));
    }

    public function store(StoreArtistRequest $request)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        unset($payload['featured_image_path']);

        $artist = DB::transaction(function () use ($payload, $image) {
            $artist = Interprete::create($this->normalizePayload($payload));
            $this->processImage($artist, $image, false);
            $this->syncImageAlt($artist, $payload);

            return $artist;
        });

        return response()->json($this->freshWithRelations($artist), 201);
    }

    public function show(Interprete $artist)
    {
        return response()->json($this->freshWithRelations($artist));
    }

    public function update(UpdateArtistRequest $request, Interprete $artist)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');
        unset($payload['featured_image_path']);

        DB::transaction(function () use ($artist, $payload, $image) {
            $artist->update($this->normalizePayload($payload, $artist));
            $this->processImage($artist, $image, true);
            $this->syncImageAlt($artist, $payload);
        });

        return response()->json($this->freshWithRelations($artist));
    }

    public function destroy(Interprete $artist)
    {
        $artist->delete();

        return response()->json(null, 204);
    }

    private function normalizePayload(array $payload, ?Interprete $artist = null): array
    {
        $name = $payload['interprete'] ?? $artist?->interprete;

        if (! $artist && empty($payload['user_id'])) {
            $payload['user_id'] = auth()->id();
        }

        if (! $artist && ! array_key_exists('estado', $payload)) {
            $payload['estado'] = false;
        }

        if (! array_key_exists('slug', $payload) && filled($name) && (! $artist || blank($artist->slug))) {
            $payload['slug'] = Str::slug($name);
        }

        if (array_key_exists('biografia', $payload)) {
            $payload['biografia'] = RichTextHeadingSanitizer::normalize($payload['biografia']);
        }

        return $payload;
    }

    private function processImage(Interprete $artist, mixed $image, bool $replace): void
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
                $artist,
                'artist',
                'interpretes',
                $replace,
                $artist->slug,
                array_merge(
                    ImageSourceMetadata::from($image),
                    ['alt' => $artist->image_alt ?: $artist->interprete]
                )
            );
        } finally {
            $this->imageResolver->cleanupTemporary($resolved);
        }
    }

    private function syncImageAlt(Interprete $artist, array $payload): void
    {
        if (! array_key_exists('image_alt', $payload)) {
            return;
        }

        $image = $artist->images()->orderBy('sort_order')->first();

        if ($image) {
            $image->update([
                'alt' => $artist->image_alt ?: $artist->interprete,
            ]);
        }
    }

    private function freshWithRelations(Interprete $artist): Interprete
    {
        return $artist->fresh([
            'images',
            'noticias',
            'events',
            'festivales',
            'discos',
            'canciones',
        ]);
    }
}
