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
    $query = Festival::query();

    if ($request->has('provincia_id')) {
      $query->where('provincia_id', $request->query('provincia_id'));
    }

    if ($request->has('mes_id')) {
      $query->where('mes_id', $request->query('mes_id'));
    }

    return response()->json($query->latest()->paginate(15));
  }

  public function store(StoreFestivalRequest $request)
  {
    $validated = $request->validated();

    if (!isset($validated['slug']) && isset($validated['titulo'])) {
      $validated['slug'] = Str::slug($validated['titulo']);
    }

    $festival = Festival::create($validated);
    return response()->json($festival, 201);
  }

  public function show(Festival $festival)
  {
    return response()->json($festival);
  }

  public function update(UpdateFestivalRequest $request, Festival $festival)
  {
    $validated = $request->validated();

    if (isset($validated['titulo']) && !isset($validated['slug'])) {
      $validated['slug'] = Str::slug($validated['titulo']);
    }

    $festival->update($validated);
    return response()->json($festival);
  }

  public function destroy(Festival $festival)
  {
    $festival->delete();
    return response()->json(null, 204);
  }
}
