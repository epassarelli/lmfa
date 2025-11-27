<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSongRequest;
use App\Http\Requests\Api\UpdateSongRequest;
use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SongController extends Controller
{
  public function index(Request $request)
  {
    $query = Cancion::query();

    if ($request->has('interprete_id')) {
      $query->where('interprete_id', $request->query('interprete_id'));
    }

    return response()->json($query->latest()->paginate(15));
  }

  public function store(StoreSongRequest $request)
  {
    $validated = $request->validated();

    if (!isset($validated['slug']) && isset($validated['cancion'])) {
      $validated['slug'] = Str::slug($validated['cancion']);
    }

    $song = Cancion::create($validated);
    return response()->json($song, 201);
  }

  public function show(Cancion $song)
  {
    return response()->json($song);
  }

  public function update(UpdateSongRequest $request, Cancion $song)
  {
    $validated = $request->validated();

    if (isset($validated['cancion']) && !isset($validated['slug'])) {
      $validated['slug'] = Str::slug($validated['cancion']);
    }

    $song->update($validated);
    return response()->json($song);
  }

  public function destroy(Cancion $song)
  {
    $song->delete();
    return response()->json(null, 204);
  }
}
