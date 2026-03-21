<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMythRequest;
use App\Http\Requests\Api\UpdateMythRequest;
use App\Models\Mito;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MythController extends Controller
{
  public function index(Request $request)
  {
    $query = Mito::query();
    return response()->json($query->latest()->paginate(15));
  }

  public function store(StoreMythRequest $request)
  {
    $validated = $request->validated();

    if (!isset($validated['slug']) && isset($validated['titulo'])) {
      $validated['slug'] = Str::slug($validated['titulo']);
    }

    $myth = Mito::create($validated);
    return response()->json($myth, 201);
  }

  public function show(Mito $myth)
  {
    return response()->json($myth);
  }

  public function update(UpdateMythRequest $request, Mito $myth)
  {
    $validated = $request->validated();

    if (isset($validated['titulo']) && !isset($validated['slug'])) {
      $validated['slug'] = Str::slug($validated['titulo']);
    }

    $myth->update($validated);
    return response()->json($myth);
  }

  public function destroy(Mito $myth)
  {
    $myth->delete();
    return response()->json(null, 204);
  }
}
