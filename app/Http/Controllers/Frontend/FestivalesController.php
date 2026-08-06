<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Services\LinkService;
use App\Support\SeoMetadata;
use Illuminate\Http\Request;

class FestivalesController extends Controller
{
    protected $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    public function index()
    {
        $festival = new Festival();
        $ultimos = $festival->getNLast(Festival::class, 12);
        $visitados = $festival->getNMostVisited(Festival::class, 30);

        $metaTitle = 'Festivales y Fiestas del Folklore Argentino: Tradicion y Cultura';
        $metaDescription = 'Descubre los festivales y fiestas tradicionales del folklore argentino. Mantente informado sobre los eventos culturales mas importantes de Argentina.';

        $breadcrumbs = [
            ['label' => 'Festivales', 'url' => route('festivales.index')],
        ];

        return view('frontend.festivales.index', compact('ultimos', 'visitados', 'metaTitle', 'metaDescription', 'breadcrumbs'));
    }

    public function show($slug)
    {
        $festival = Festival::where('slug', $slug)->with('images')->firstOrFail();

        $ultimos_festivales = Festival::where('estado', 1)
            ->where('id', '<>', $festival->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $festival->increment('visitas');

        $seo = SeoMetadata::festival($festival);
        $metaTitle = $seo['title'];
        $metaDescription = $seo['description'];
        $h1 = $seo['h1'];

        $breadcrumbs = [
            ['label' => 'Festivales', 'url' => route('festivales.index')],
            ['label' => $festival->titulo],
        ];

        $festival->detalle = $this->linkService->autoLinkArtists($festival->detalle);

        return view('frontend.festivales.show', compact('festival', 'ultimos_festivales', 'metaTitle', 'metaDescription', 'h1', 'breadcrumbs'));
    }
}
