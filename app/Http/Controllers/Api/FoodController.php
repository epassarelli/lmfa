<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFoodRequest;
use App\Http\Requests\Api\UpdateFoodRequest;
use App\Models\Comida;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FoodController extends Controller
{
  public function index(Request $request)
  {
    $query = Comida::query();
    return response()->json($query->latest()->paginate(15));
  }

  public function store(StoreFoodRequest $request)
  {
    $validated = $request->validated();

    if (!isset($validated['slug']) && isset($validated['titulo'])) {
      $validated['slug'] = Str::slug($validated['titulo']);
    }

    $food = Comida::create($validated);
    return response()->json($food, 201);
  }

  public function show(Comida $food)
  {
    return response()->json($food);
  }

  public function update(UpdateFoodRequest $request, Comida $food)
  {
    $validated = $request->validated();

    if (isset($validated['titulo']) && !isset($validated['slug'])) {
      $validated['slug'] = Str::slug($validated['titulo']);
    }

    $food->update($validated);
    return response()->json($food);
  }

  public function destroy(Comida $food)
  {
    $food->delete();
    return response()->json(null, 204);
  }
}
