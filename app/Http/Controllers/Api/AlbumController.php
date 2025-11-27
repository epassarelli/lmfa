<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAlbumRequest;
use App\Http\Requests\Api\UpdateAlbumRequest;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
  public function index(Request $request)
  {
    $query = Album::query();

    if ($request->has('interprete_id')) {
      $query->where('interprete_id', $request->query('interprete_id'));
    }

    return response()->json($query->latest()->paginate(15));
  }

  public function store(StoreAlbumRequest $request)
  {
    $validated = $request->validated();

    if (!isset($validated['slug']) && isset($validated['album'])) {
      $validated['slug'] = Str::slug($validated['album']);
    }

    $album = Album::create($validated);
    return response()->json($album, 201);
  }

  public function show(Album $album)
  {
    return response()->json($album);
  }

  public function update(UpdateAlbumRequest $request, Album $album)
  {
    $validated = $request->validated();

    if (isset($validated['album']) && !isset($validated['slug'])) {
      $validated['slug'] = Str::slug($validated['album']);
    }

    $album->update($validated);
    return response()->json($album);
  }

  public function destroy(Album $album)
  {
    $album->delete();
    return response()->json(null, 204);
  }
}
