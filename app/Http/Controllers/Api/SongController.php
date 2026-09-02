<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSongRequest;
use App\Http\Requests\Api\UpdateSongRequest;
use App\Models\Cancion;
use App\Support\RichTextHeadingSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SongController extends Controller
{
    public function index(Request $request)
    {
        $query = Cancion::query()->with(['interprete', 'albunes']);

        if ($request->has('interprete_id')) {
            $query->where('interprete_id', $request->query('interprete_id'));
        }

        if ($request->boolean('active_only')) {
            $query->where('estado', 1);
        }

        if ($request->filled('rights_status')) {
            $query->where('rights_status', $request->query('rights_status'));
        }

        return response()->json($query->latest('id')->paginate(50));
    }

    public function store(StoreSongRequest $request)
    {
        $payload = $request->validated();

        $song = DB::transaction(function () use ($payload) {
            $song = Cancion::create($this->normalizePayload($payload));
            $this->syncAlbums($song, $payload, true);

            return $song;
        });

        return response()->json($this->freshWithRelations($song), 201);
    }

    public function show(Cancion $song)
    {
        return response()->json($this->freshWithRelations($song));
    }

    public function update(UpdateSongRequest $request, Cancion $song)
    {
        $payload = $request->validated();

        DB::transaction(function () use ($song, $payload) {
            $song->update($this->normalizePayload($payload, $song));
            $this->syncAlbums($song, $payload);
        });

        return response()->json($this->freshWithRelations($song));
    }

    public function destroy(Cancion $song)
    {
        $song->delete();

        return response()->json(null, 204);
    }

    private function normalizePayload(array $payload, ?Cancion $song = null): array
    {
        $name = $payload['cancion'] ?? $song?->cancion;

        if (! $song && empty($payload['user_id'])) {
            $payload['user_id'] = auth()->id();
        }

        if (! $song && ! array_key_exists('estado', $payload)) {
            $payload['estado'] = false;
        }

        if (! $song && ! array_key_exists('visitas', $payload)) {
            $payload['visitas'] = 0;
        }

        if (! $song && ! array_key_exists('rights_status', $payload)) {
            $payload['rights_status'] = 'unknown';
        }

        if (! array_key_exists('slug', $payload) && filled($name) && (! $song || blank($song->slug))) {
            $payload['slug'] = Str::slug($name);
        }

        if (array_key_exists('letra', $payload) && filled($payload['letra'])) {
            $payload['letra'] = RichTextHeadingSanitizer::normalize($payload['letra']);
        }

        $isInstrumental = filter_var(
            $payload['is_instrumental'] ?? $song?->is_instrumental ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if ($isInstrumental) {
            $payload['is_instrumental'] = true;
            $payload['letra'] = null;
            $payload['lyricist'] = null;
            $payload['rights_status'] = $payload['rights_status'] ?? 'not_available';
        }

        unset($payload['album_ids']);

        return $payload;
    }

    private function syncAlbums(Cancion $song, array $payload, bool $creating = false): void
    {
        if ($creating || array_key_exists('album_ids', $payload)) {
            $song->albunes()->sync($payload['album_ids'] ?? []);
        }
    }

    private function freshWithRelations(Cancion $song): Cancion
    {
        return $song->fresh(['interprete', 'albunes']);
    }
}
