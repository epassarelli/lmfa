<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Comida;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\News;
use App\Support\SeoMetadata;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    private const NEWS_LIMIT = 24;

    public function index()
    {
        $ultimasNoticias = Cache::remember('home:ultimas-noticias', now()->addMinutes(10), function () {
            return News::publishedVisible()
                ->with(['categoria:id,nombre', 'images', 'interprete:id,interprete,slug,foto', 'interprete.images'])
                ->latest('published_at')
                ->latest('created_at')
                ->take(self::NEWS_LIMIT)
                ->get();
        });

        $destacados = Cache::remember('home:destacados', now()->addMinutes(30), function () {
            return [
                'artista' => Interprete::where('estado', 1)->with('images')->orderByDesc('visitas')->first(),
                'disco' => Album::where('estado', 1)->with(['images', 'interprete'])->orderByDesc('visitas')->first(),
                'festival' => Festival::publishedVisible()->with('images')->orderByDesc('visitas')->first(),
                'receta' => Comida::where('estado', 1)->with('images')->orderByDesc('visitas')->first(),
            ];
        });

        $seo = SeoMetadata::home();

        return view('frontend.home', [
            'metaTitle' => $seo['title'],
            'metaDescription' => $seo['description'],
            'h1' => $seo['h1'],
            'ultimasNoticias' => $ultimasNoticias,
            'destacados' => $destacados,
        ]);
    }
}
