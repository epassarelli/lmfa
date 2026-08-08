<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFestivalRequest;
use App\Http\Requests\Api\UpdateFestivalRequest;
use App\Models\Festival;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FestivalController extends Controller
{
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
        $festival = Festival::create($this->normalizePayload($request->validated()));

        return response()->json($festival, 201);
    }

    public function show(Festival $festival)
    {
        return response()->json($festival->load(['provincia', 'mes', 'locality']));
    }

    public function update(UpdateFestivalRequest $request, Festival $festival)
    {
        $festival->update($this->normalizePayload($request->validated(), $festival));

        return response()->json($festival);
    }

    public function destroy(Festival $festival)
    {
        $festival->delete();

        return response()->json(null, 204);
    }

    private function normalizePayload(array $validated, ?Festival $festival = null): array
    {
        $title = $validated['title'] ?? $festival?->title;
        $body = $validated['body'] ?? $festival?->body;

        if (! isset($validated['slug']) && filled($title)) {
            $validated['slug'] = Str::slug($title);
        }

        $validated['title'] = $title;
        $validated['body'] = $body;

        if (! array_key_exists('published_at', $validated) && $festival?->published_at) {
            $validated['published_at'] = $festival->published_at;
        }

        if (! array_key_exists('status', $validated) && $festival?->status) {
            $validated['status'] = $festival->status;
        }

        return $validated;
    }
}
