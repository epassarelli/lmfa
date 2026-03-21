<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreArtistRequest;
use App\Http\Requests\Api\UpdateArtistRequest;
use App\Models\Interprete;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArtistController extends Controller
{
  public function index(Request $request)
  {
    $query = Interprete::query();
    return response()->json($query->orderBy('interprete')->paginate(15));
  }

  public function store(StoreArtistRequest $request)
  {
    $validated = $request->validated();

    if (!isset($validated['slug']) && isset($validated['interprete'])) {
      $validated['slug'] = Str::slug($validated['interprete']);
    }

    $artist = Interprete::create($validated);
    return response()->json($artist, 201);
  }

  public function show(Interprete $artist)
  {
    return response()->json($artist);
  }

  public function update(UpdateArtistRequest $request, Interprete $artist)
  {
    $validated = $request->validated();

    if (isset($validated['interprete']) && !isset($validated['slug'])) {
      $validated['slug'] = Str::slug($validated['interprete']);
    }

    $artist->update($validated);
    return response()->json($artist);
  }

  public function destroy(Interprete $artist)
  {
    $artist->delete();
    return response()->json(null, 204);
  }
}
