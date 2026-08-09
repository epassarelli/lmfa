<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Support\SeoMetadata;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $ultimasNoticias = Cache::remember('home:ultimas-noticias', now()->addMinutes(10), function () {
            return News::publishedVisible()
                ->with(['categoria:id,nombre', 'images'])
                ->latest('published_at')
                ->latest('created_at')
                ->take(12)
                ->get();
        });

        $seo = SeoMetadata::home();

        return view('frontend.home', [
            'metaTitle' => $seo['title'],
            'metaDescription' => $seo['description'],
            'h1' => $seo['h1'],
            'ultimasNoticias' => $ultimasNoticias,
        ]);
    }
}
