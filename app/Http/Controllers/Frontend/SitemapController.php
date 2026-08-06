<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Comida;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Mito;
use App\Models\News;
use App\Models\Penia;
use App\Models\Radio;
use App\Support\CanonicalUrl;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SitemapController extends Controller
{
    public function index(): Response
    {
        return $this->xmlResponse('sitemap-index');
    }

    public function main(): Response
    {
        return $this->xmlResponse('sitemap', [
            'urls' => $this->mainEntries(),
        ]);
    }

    public function newsIndex(): Response
    {
        $noticias = News::query()
            ->where('editorial_status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('published_at', '>=', now()->subDays(2))
            ->orderByDesc('published_at')
            ->get();

        return $this->xmlResponse('sitemap-news', compact('noticias'));
    }

    protected function mainEntries(): Collection
    {
        $urls = collect([
            $this->entry(route('home'), '1.0', 'daily'),
            $this->entry(route('contacto'), '0.5', 'monthly'),
            $this->entry(route('noticias.index'), '0.8', 'daily'),
            $this->entry(route('cartelera.index'), '0.8', 'daily'),
            $this->entry(route('interpretes.index'), '0.8', 'weekly'),
            $this->entry(route('canciones.index'), '0.8', 'weekly'),
            $this->entry(route('discografias.index'), '0.8', 'weekly'),
            $this->entry(route('festivales.index'), '0.8', 'weekly'),
            $this->entry(route('enciclopedia.index'), '0.8', 'weekly'),
            $this->entry(route('radios.index'), '0.8', 'monthly'),
            $this->entry(route('penias.index'), '0.8', 'monthly'),
            $this->entry(route('mitos.index'), '0.8', 'monthly'),
            $this->entry(route('comidas.index'), '0.8', 'monthly'),
            $this->entry(route('folklore.cup.index'), '0.6', 'weekly'),
        ]);

        $news = News::query()
            ->with('images')
            ->where('editorial_status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (News $news) => $this->entry(
                route('noticias.show', $news->slug),
                '0.9',
                'weekly',
                $news->updated_at?->toAtomString(),
                $this->imageUrl($news->images->first()?->original_path, $news->featured_image_path ? 'storage/'.$news->featured_image_path : null)
            ));

        $interpretes = Interprete::query()
            ->where('estado', 1)
            ->get()
            ->flatMap(function (Interprete $interprete) {
                return [
                    $this->entry(route('artista.show', $interprete->slug), '0.8', 'monthly', $interprete->updated_at?->toAtomString()),
                    $this->entry(route('artista.biografia', $interprete->slug), '0.7', 'monthly', $interprete->updated_at?->toAtomString()),
                ];
            });

        $shows = Event::query()
            ->where('editorial_status', 'published')
            ->where('start_at', '>=', now()->startOfDay())
            ->orderBy('start_at')
            ->get()
            ->map(fn (Event $show) => $this->entry(
                route('cartelera.show', $show->slug),
                '0.8',
                'weekly',
                $show->updated_at?->toAtomString()
            ));

        $discos = Album::query()
            ->with(['interprete', 'images'])
            ->where('estado', 1)
            ->get()
            ->filter(fn (Album $album) => filled($album->slug) && filled($album->interprete?->slug))
            ->map(fn (Album $album) => $this->entry(
                route('artista.disco', ['interprete' => $album->interprete->slug, 'slug' => $album->slug]),
                '0.7',
                'monthly',
                $album->updated_at?->toAtomString(),
                $this->imageUrl($album->images->first()?->original_path, $album->foto ? 'storage/albunes/'.$album->foto : null)
            ));

        $canciones = Cancion::query()
            ->with('interprete')
            ->where('estado', 1)
            ->get()
            ->filter(fn (Cancion $cancion) => filled($cancion->slug) && filled($cancion->interprete?->slug))
            ->map(fn (Cancion $cancion) => $this->entry(
                route('artista.cancion', ['interprete' => $cancion->interprete->slug, 'cancion' => $cancion->slug]),
                '0.7',
                'monthly',
                $cancion->updated_at?->toAtomString()
            ));

        $festivales = Festival::query()
            ->with('images')
            ->where('estado', 1)
            ->get()
            ->map(fn (Festival $festival) => $this->entry(
                route('festivales.show', $festival->slug),
                '0.8',
                'monthly',
                $festival->updated_at?->toAtomString(),
                $this->imageUrl($festival->images->first()?->original_path, $festival->foto ? 'storage/festivales/'.$festival->foto : null)
            ));

        $knowledgeCategories = KnowledgeCategory::active()
            ->get()
            ->map(fn (KnowledgeCategory $category) => $this->entry(
                route('enciclopedia.category', $category->slug),
                '0.7',
                'weekly',
                $category->updated_at?->toAtomString()
            ));

        $knowledgeArticles = KnowledgeArticle::query()
            ->visible()
            ->with(['category', 'images'])
            ->get()
            ->filter(fn (KnowledgeArticle $article) => filled($article->category?->slug))
            ->map(fn (KnowledgeArticle $article) => $this->entry(
                route('enciclopedia.show', ['categorySlug' => $article->category->slug, 'articleSlug' => $article->slug]),
                '0.8',
                'monthly',
                $article->updated_at?->toAtomString(),
                $this->imageUrl($article->images->first()?->original_path, $article->featured_image_path ? 'storage/'.$article->featured_image_path : null)
            ));

        $radios = Radio::query()
            ->where('estado', 1)
            ->get()
            ->map(fn (Radio $radio) => $this->entry(route('radios.show', $radio->slug), '0.6', 'monthly', $radio->updated_at?->toAtomString()));

        $penias = Penia::query()
            ->where('estado', 1)
            ->get()
            ->map(fn (Penia $penia) => $this->entry(route('penias.show', $penia->slug), '0.6', 'monthly', $penia->updated_at?->toAtomString()));

        $mitos = Mito::query()
            ->where('estado', 1)
            ->get()
            ->map(fn (Mito $mito) => $this->entry(route('mitos.show', $mito->slug), '0.7', 'monthly', $mito->updated_at?->toAtomString()));

        $recetas = Comida::query()
            ->with('images')
            ->where('estado', 1)
            ->get()
            ->map(fn (Comida $comida) => $this->entry(
                route('comidas.show', $comida->slug),
                '0.7',
                'monthly',
                $comida->updated_at?->toAtomString(),
                $this->imageUrl($comida->images->first()?->original_path, $comida->foto ? 'storage/comidas/'.$comida->foto : null)
            ));

        return $urls
            ->merge($news)
            ->merge($interpretes)
            ->merge($shows)
            ->merge($discos)
            ->merge($canciones)
            ->merge($festivales)
            ->merge($knowledgeCategories)
            ->merge($knowledgeArticles)
            ->merge($radios)
            ->merge($penias)
            ->merge($mitos)
            ->merge($recetas)
            ->values();
    }

    protected function entry(string $url, string $priority, string $changefreq, ?string $lastmod = null, ?string $image = null): array
    {
        return array_filter([
            'url' => CanonicalUrl::normalize($url),
            'priority' => $priority,
            'changefreq' => $changefreq,
            'lastmod' => $lastmod ?? now()->toAtomString(),
            'image' => $image,
        ]);
    }

    protected function imageUrl(?string $primaryPath = null, ?string $fallbackPath = null): ?string
    {
        $path = $primaryPath ?: $fallbackPath;

        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return CanonicalUrl::normalize($path);
        }

        $publicPath = str_starts_with($path, 'storage/')
            ? '/'.$path
            : Storage::disk('public')->url($path);

        return CanonicalUrl::asset($publicPath);
    }

    protected function xmlResponse(string $view, array $data = []): Response
    {
        return response()
            ->view($view, $data, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
