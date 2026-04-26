<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreNewsRequest;
use App\Http\Requests\Api\UpdateNewsRequest;
use App\Models\News;
use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
  protected $newsService;

  public function __construct(NewsService $newsService)
  {
    $this->newsService = $newsService;
  }
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = News::query();

    // Optional filtering can be added here
    if ($request->has('categoria_id')) {
      $query->where('categoria_id', $request->query('categoria_id'));
    }

    if ($request->has('estado')) {
      $query->where('estado', $request->query('estado'));
    }

    $news = $query->latest()->paginate(15);

    return response()->json($news);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreNewsRequest $request)
  {
    $news = $this->newsService->createNews(
      $request->validated(),
      $request->file('foto') // Soporta subida de archivos si se envía
    );

    return response()->json($news, 201);
  }

  /**
   * Display the specified resource.
   */
  public function show(News $news)
  {
    return response()->json($news);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateNewsRequest $request, News $news)
  {
    $news = $this->newsService->updateNews(
      $news,
      $request->validated(),
      $request->file('foto')
    );

    return response()->json($news);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(News $news)
  {
    $news->delete();

    return response()->json(null, 204);
  }
}
