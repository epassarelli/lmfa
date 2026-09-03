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
use App\Models\PeniaProfile;
use App\Models\RadioSignal;
use App\Support\CanonicalUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index(): Response
    {
        return $this->xmlResponse('sitemap-index', [
            'sitemaps' => $this->sitemapIndexEntries(),
        ]);
    }

    public function legacyMain(): RedirectResponse
    {
        return redirect()->to(CanonicalUrl::normalize(route('sitemap.index', [], false)), 301);
    }

    public function legacyGoogleNews(): RedirectResponse
    {
        return redirect()->to(CanonicalUrl::normalize(route('sitemap.google-news', [], false)), 301);
    }

    public function staticPages(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->staticEntries(),
        ]);
    }

    public function artists(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->artistHubEntries(),
        ]);
    }

    public function biographies(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->artistBiographyEntries(),
        ]);
    }

    public function news(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->newsEntries(),
        ]);
    }

    public function googleNews(): Response
    {
        return $this->xmlResponse('sitemap-google-news', [
            'noticias' => $this->googleNewsItems(),
        ]);
    }

    public function events(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->eventEntries(),
        ]);
    }

    public function festivals(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->festivalEntries(),
        ]);
    }

    public function penias(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->peniaEntries(),
        ]);
    }

    public function radios(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->radioEntries(),
        ]);
    }

    public function discographies(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->discographyEntries(),
        ]);
    }

    public function lyrics(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->lyricEntries(),
        ]);
    }

    public function evergreen(): Response
    {
        return $this->xmlResponse('sitemap-urlset', [
            'urls' => $this->evergreenEntries(),
        ]);
    }

    protected function sitemapIndexEntries(): Collection
    {
        return $this->uniqueEntries(collect([
            $this->sitemapIndexEntry(route('sitemap.static'), $this->staticEntries()),
            $this->sitemapIndexEntry(route('sitemap.artists'), $this->artistHubEntries()),
            $this->sitemapIndexEntry(route('sitemap.biographies'), $this->artistBiographyEntries()),
            $this->sitemapIndexEntry(route('sitemap.news'), $this->newsEntries()),
            $this->sitemapIndexEntry(route('sitemap.google-news'), $this->googleNewsEntries()),
            $this->sitemapIndexEntry(route('sitemap.events'), $this->eventEntries()),
            $this->sitemapIndexEntry(route('sitemap.festivals'), $this->festivalEntries()),
            $this->sitemapIndexEntry(route('sitemap.penias'), $this->peniaEntries()),
            $this->sitemapIndexEntry(route('sitemap.radios'), $this->radioEntries()),
            $this->sitemapIndexEntry(route('sitemap.discographies'), $this->discographyEntries()),
            $this->sitemapIndexEntry(route('sitemap.lyrics'), $this->lyricEntries()),
            $this->sitemapIndexEntry(route('sitemap.evergreen'), $this->evergreenEntries()),
        ]));
    }

    protected function staticEntries(): Collection
    {
        return $this->uniqueEntries(collect([
            $this->entry(route('home')),
            $this->entry(route('contacto')),
            $this->entry(route('noticias.index')),
            $this->entry(route('cartelera.index')),
            $this->entry(route('interpretes.index')),
            $this->entry(route('canciones.index')),
            $this->entry(route('discografias.index')),
            $this->entry(route('festivales.index')),
            $this->entry(route('penia-profiles.index')),
            $this->entry(route('radios.index')),
            $this->entry(route('enciclopedia.index')),
            $this->entry(route('mitos.index')),
            $this->entry(route('comidas.index')),
            $this->entry(route('folklore.cup.index')),
            $this->entry(route('folklore.cup.participants')),
            $this->entry(route('folklore.cup.fixture')),
            $this->entry(route('folklore.cup.groups')),
            $this->entry(route('folklore.cup.bracket')),
            $this->entry(route('folklore.cup.rules')),
        ])->merge($this->recipeLetterEntries()));
    }

    protected function artistHubEntries(): Collection
    {
        return $this->uniqueEntries(Interprete::query()
            ->where('estado', 1)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get()
            ->map(fn (Interprete $interprete) => $this->entry(
                route('artista.show', $interprete->slug),
                $this->bestDate($interprete->updated_at, $interprete->created_at)
            )));
    }

    protected function artistBiographyEntries(): Collection
    {
        return $this->uniqueEntries(Interprete::query()
            ->where('estado', 1)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get()
            ->map(fn (Interprete $interprete) => $this->entry(
                route('artista.biografia', $interprete->slug),
                $this->bestDate($interprete->updated_at, $interprete->created_at)
            )));
    }

    protected function newsEntries(): Collection
    {
        return $this->uniqueEntries(News::query()
            ->where('editorial_status', 'published')
            ->whereNotNull('slug')
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderBy('id')
            ->get()
            ->map(fn (News $news) => $this->entry(
                route('noticias.show', $news->slug),
                $this->bestDate($news->updated_at, $news->published_at, $news->created_at)
            )));
    }

    protected function googleNewsItems(): Collection
    {
        return News::query()
            ->where('editorial_status', 'published')
            ->whereNotNull('slug')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('published_at', '>=', now()->subDays(2))
            ->orderByDesc('published_at')
            ->get();
    }

    protected function googleNewsEntries(): Collection
    {
        return $this->uniqueEntries($this->googleNewsItems()
            ->map(fn (News $news) => $this->entry(
                route('noticias.show', $news->slug),
                $this->bestDate($news->published_at)
            )));
    }

    protected function eventEntries(): Collection
    {
        return $this->uniqueEntries(Event::query()
            ->where('editorial_status', 'published')
            ->whereNotNull('slug')
            ->where('start_at', '>=', now()->startOfDay())
            ->orderBy('id')
            ->get()
            ->map(fn (Event $event) => $this->entry(
                route('cartelera.show', $event->slug),
                $this->bestDate($event->updated_at, $event->published_at, $event->created_at, $event->start_at)
            )));
    }

    protected function festivalEntries(): Collection
    {
        $festivalDetails = Festival::query()
            ->publishedVisible()
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get()
            ->map(fn (Festival $festival) => $this->entry(
                route('festivales.show', $festival->slug),
                $this->bestDate($festival->updated_at, $festival->published_at, $festival->created_at)
            ));

        $provinceEntries = Festival::query()
            ->publishedVisible()
            ->with('provincia')
            ->get()
            ->pluck('provincia')
            ->filter()
            ->unique('id')
            ->map(fn ($province) => $this->entry(route('festivales.province', $province->slug)));

        $monthEntries = Festival::query()
            ->publishedVisible()
            ->with('mes')
            ->get()
            ->pluck('mes')
            ->filter()
            ->unique('id')
            ->map(fn ($month) => $this->entry(route('festivales.month', \Illuminate\Support\Str::slug($month->nombre))));

        $minimum = (int) config('festivals.province_month_indexable_minimum', 3);
        $provinceMonthEntries = Festival::query()
            ->publishedVisible()
            ->selectRaw('province_id, mes_id, COUNT(*) as total')
            ->groupBy('province_id', 'mes_id')
            ->havingRaw('COUNT(*) >= ?', [$minimum])
            ->get()
            ->map(function ($row) {
                $province = \App\Models\Provincia::find($row->province_id);
                $month = \App\Models\Mes::find($row->mes_id);

                if (! $province || ! $month) {
                    return null;
                }

                return $this->entry(route('festivales.province-month', [$province->slug, \Illuminate\Support\Str::slug($month->nombre)]));
            })
            ->filter();

        return $this->uniqueEntries(
            $festivalDetails
                ->merge($provinceEntries)
                ->merge($monthEntries)
                ->merge($provinceMonthEntries)
        );
    }

    protected function peniaEntries(): Collection
    {
        return $this->uniqueEntries(PeniaProfile::query()
            ->publiclyVisible()
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get()
            ->map(fn (PeniaProfile $penia) => $this->entry(
                $penia->getUrl(),
                $this->bestDate($penia->updated_at, $penia->last_verified_at, $penia->published_at, $penia->created_at)
            )));
    }

    protected function radioEntries(): Collection
    {
        return $this->uniqueEntries(RadioSignal::query()
            ->publiclyVisible()
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get()
            ->map(fn (RadioSignal $signal) => $this->entry(
                route('radios.show', $signal->slug),
                $this->bestDate($signal->updated_at, $signal->last_verified_at, $signal->published_at, $signal->created_at)
            )));
    }

    protected function discographyEntries(): Collection
    {
        return $this->uniqueEntries(Album::query()
            ->with('interprete')
            ->where('estado', 1)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get()
            ->filter(fn (Album $album) => filled($album->interprete?->slug))
            ->map(fn (Album $album) => $this->entry(
                route('artista.disco', ['interprete' => $album->interprete->slug, 'slug' => $album->slug]),
                $this->bestDate($album->updated_at, $album->created_at)
            ))
            ->values());
    }

    protected function lyricEntries(): Collection
    {
        return $this->uniqueEntries(Cancion::query()
            ->with('interprete')
            ->where('estado', 1)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get()
            ->filter(fn (Cancion $cancion) => filled($cancion->interprete?->slug))
            ->map(fn (Cancion $cancion) => $this->entry(
                route('artista.cancion', ['interprete' => $cancion->interprete->slug, 'cancion' => $cancion->slug]),
                $this->bestDate($cancion->updated_at, $cancion->created_at)
            ))
            ->values());
    }

    protected function evergreenEntries(): Collection
    {
        $knowledgeCategories = KnowledgeCategory::active()
            ->get()
            ->map(fn (KnowledgeCategory $category) => $this->entry(
                route('enciclopedia.category', $category->slug),
                $this->bestDate($category->updated_at, $category->created_at)
            ));

        $knowledgeArticles = KnowledgeArticle::query()
            ->visible()
            ->with('category')
            ->orderBy('id')
            ->get()
            ->filter(fn (KnowledgeArticle $article) => filled($article->category?->slug))
            ->map(fn (KnowledgeArticle $article) => $this->entry(
                route('enciclopedia.show', [
                    'categorySlug' => $article->category->slug,
                    'articleSlug' => $article->slug,
                ]),
                $this->bestDate($article->updated_at, $article->published_at, $article->created_at)
            ));

        $myths = Mito::query()
            ->where('estado', 1)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get()
            ->map(fn (Mito $mito) => $this->entry(
                route('mitos.show', $mito->slug),
                $this->bestDate($mito->updated_at, $mito->created_at)
            ));

        $foods = Comida::query()
            ->where('estado', 1)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get()
            ->map(fn (Comida $comida) => $this->entry(
                route('comidas.show', $comida->slug),
                $this->bestDate($comida->updated_at, $comida->created_at)
            ));

        return $this->uniqueEntries($knowledgeCategories
            ->merge($knowledgeArticles)
            ->merge($myths)
            ->merge($foods)
            ->values());
    }

    protected function recipeLetterEntries(): Collection
    {
        return Comida::query()
            ->where('estado', 1)
            ->whereNotNull('titulo')
            ->orderBy('titulo')
            ->get()
            ->map(function (Comida $comida) {
                $letter = Str::lower(Str::substr(ltrim((string) $comida->titulo), 0, 1));

                if (! preg_match('/^[a-z]$/', $letter)) {
                    return null;
                }

                return $this->entry(route('comidas.letra', $letter));
            })
            ->filter()
            ->values();
    }

    protected function sitemapIndexEntry(string $url, Collection $entries): array
    {
        return array_filter([
            'url' => CanonicalUrl::normalize($url),
            'lastmod' => $this->maxLastmod($entries),
        ]);
    }

    protected function entry(string $url, ?string $lastmod = null): array
    {
        return array_filter([
            'url' => CanonicalUrl::normalize($url),
            'lastmod' => $lastmod,
        ]);
    }

    protected function bestDate(...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (blank($candidate)) {
                continue;
            }

            return $candidate->toAtomString();
        }

        return null;
    }

    protected function maxLastmod(Collection $entries): ?string
    {
        return $entries
            ->pluck('lastmod')
            ->filter()
            ->sort()
            ->last();
    }

    protected function uniqueEntries(Collection $entries): Collection
    {
        return $entries
            ->unique('url')
            ->values();
    }

    protected function xmlResponse(string $view, array $data = []): Response
    {
        return response()
            ->view($view, $data, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
