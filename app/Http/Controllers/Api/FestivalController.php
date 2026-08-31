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
            $this->processImage($festival, $image, false);

            return $festival;
        });

        return response()->json($festival->fresh('images'), 201);
    }

    public function show(Festival $festival)
    {
        return response()->json($festival->load(['provincia', 'mes', 'locality']));
    }

    public function update(UpdateFestivalRequest $request, Festival $festival)
    {
        $payload = $request->validated();
        $image = ApiImageInput::extract($request, $payload, 'featured_image');

        DB::transaction(function () use ($festival, $payload, $image) {
            $festival->update($this->normalizePayload($payload, $festival));
            $this->processImage($festival, $image, true);
        });

        return response()->json($festival->fresh('images'));
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
                ImageSourceMetadata::from($image)
            );

            $festival->forceFill([
                'featured_image_id' => $media->id,
                'featured_image_path' => $media->path,
            ])->save();
        } finally {
            if ($resolved instanceof \Illuminate\Http\UploadedFile && str_contains($resolved->getPathname(), 'tmp/news-images')) {
                @unlink($resolved->getPathname());
            }
        }
    }

    private function normalizePayload(array $validated, ?Festival $festival = null): array
    {
        $title = $validated['title'] ?? $festival?->title;
        $body = $validated['body'] ?? $festival?->body;

        if (! isset($validated['slug']) && filled($title)) {
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

        return $validated;
    }
}
