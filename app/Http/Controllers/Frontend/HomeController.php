<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\News;
use App\Support\SeoMetadata;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    private const NEWS_LIMIT = 24;
    private const BLOCK_SIZE = 4;
    public const BLOCKS_CACHE_KEY = 'home:bloques';

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

        $catalogo = Cache::remember(self::BLOCKS_CACHE_KEY, now()->addMinutes(10), function () {
            return [
                'eventos' => Event::publiclyVisible()->upcomingOrOngoing()
                    ->with(['interpretes.images', 'images', 'program.images'])
                    ->orderBy('start_at')
                    ->take(self::BLOCK_SIZE)
                    ->get(),
                'interpretes' => Interprete::where('estado', 1)
                    ->with('images')
                    ->orderByDesc('id')
                    ->take(self::BLOCK_SIZE)
                    ->get(),
                'festivales' => Festival::publishedVisible()
                    ->with('images')
                    ->orderByDesc('visitas')
                    ->take(self::BLOCK_SIZE)
                    ->get(),
                'discos' => Album::where('estado', 1)
                    ->with(['images', 'interprete'])
                    ->orderByDesc('id')
                    ->take(self::BLOCK_SIZE)
                    ->get(),
            ];
        });

        $seo = SeoMetadata::home();

        return view('frontend.home', [
            'metaTitle' => $seo['title'],
            'metaDescription' => $seo['description'],
            'h1' => $seo['h1'],
            'ultimasNoticias' => $ultimasNoticias,
            'catalogo' => $catalogo,
        ]);
    }
}
