<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Support\CanonicalUrl;
use Illuminate\Support\Str;

class KnowledgeController extends Controller
{
    private const CATEGORY_LANDING_COPY = [
        'ritmos' => [
            'intro' => 'Esta seccion reune contenidos pensados para entender los ritmos del folklore argentino desde su estructura, su origen y su uso en la practica musical. Es una puerta de entrada util para distinguir chacarera, zamba, chamame, gato y otras formas centrales del repertorio tradicional.',
            'list_heading' => 'Articulos sobre ritmos del folklore argentino',
            'empty_heading' => 'Todavia no hay articulos publicados sobre ritmos',
            'empty_text' => 'Esta landing va a reunir contenidos sobre chacarera, zamba, chamame, gato y otros ritmos fundamentales del folklore argentino.',
        ],
        'danzas' => [
            'intro' => 'En esta landing vas a encontrar contenidos para conocer mejor las danzas folkloricas argentinas, su contexto cultural y sus formas de baile. El foco esta puesto en explicar pasos, figuras, estilos y diferencias de manera clara para quien quiere aprender o profundizar.',
            'list_heading' => 'Guias y articulos sobre danzas folkloricas argentinas',
            'empty_heading' => 'Todavia no hay articulos publicados sobre danzas',
            'empty_text' => 'Proximamente esta seccion reunira guias para entender y bailar las principales danzas folkloricas argentinas.',
        ],
        'instrumentos' => [
            'intro' => 'Esta categoria esta orientada a quienes buscan comprender que instrumentos forman el sonido del folklore argentino y como participa cada uno en peñas, conjuntos, grabaciones y escenarios. Vas a encontrar fichas claras sobre funcion, timbre, uso e identidad musical.',
            'list_heading' => 'Instrumentos del folklore argentino explicados paso a paso',
            'empty_heading' => 'Todavia no hay articulos publicados sobre instrumentos',
            'empty_text' => 'Esta seccion va a concentrar contenidos sobre bombo leguero, guitarra, violin, charango y otros instrumentos del folklore argentino.',
        ],
        'regiones' => [
            'intro' => 'El folklore argentino cambia segun la region, y esta landing esta pensada para ordenar esa diversidad. Reune contenidos para comprender mejor los rasgos musicales, culturales e historicos del NOA, Litoral, Cuyo, Centro y Patagonia.',
            'list_heading' => 'Regiones del folklore argentino y sus rasgos distintivos',
            'empty_heading' => 'Todavia no hay articulos publicados sobre regiones',
            'empty_text' => 'Aca se iran sumando contenidos para explicar como se expresa el folklore argentino en cada region del pais.',
        ],
        'provincias' => [
            'intro' => 'Esta landing organiza el folklore argentino por provincias para facilitar una lectura territorial del tema. Es un espacio util para descubrir referentes, ritmos, fiestas populares y tradiciones que le dan identidad propia a cada provincia.',
            'list_heading' => 'Folklore argentino por provincias: guias y referencias',
            'empty_heading' => 'Todavia no hay articulos publicados sobre provincias',
            'empty_text' => 'Esta seccion reunira contenidos por provincia para conectar artistas, ritmos y tradiciones con cada territorio argentino.',
        ],
        'historia' => [
            'intro' => 'La historia del folklore argentino necesita contexto, procesos y nombres propios. Esta landing esta pensada para reunir articulos que ayuden a entender origenes, etapas de expansion, momentos de cambio y figuras clave del desarrollo folklorico en la Argentina.',
            'list_heading' => 'Historia del folklore argentino: procesos, etapas y protagonistas',
            'empty_heading' => 'Todavia no hay articulos publicados sobre historia',
            'empty_text' => 'Proximamente esta categoria reunira articulos para explicar el desarrollo historico del folklore argentino y sus hitos principales.',
        ],
        'tradiciones' => [
            'intro' => 'Las tradiciones mantienen vivo al folklore mas alla del escenario, y esta landing busca mostrar justamente esa dimension cotidiana y comunitaria. Aca se agrupan contenidos sobre peñas, costumbres, celebraciones y practicas culturales ligadas al mundo folklorico.',
            'list_heading' => 'Tradiciones del folklore argentino en peñas, fiestas y costumbres',
            'empty_heading' => 'Todavia no hay articulos publicados sobre tradiciones',
            'empty_text' => 'Esta seccion va a concentrar contenidos sobre peñas folkloricas, celebraciones y costumbres vinculadas al folklore argentino.',
        ],
        'cancionero' => [
            'intro' => 'El cancionero folklorico argentino es una puerta privilegiada para entender memoria, territorio y sensibilidad popular. Esta landing reune contenidos sobre canciones emblematicas, su historia, su contexto y el significado que ganaron con el tiempo.',
            'list_heading' => 'Cancionero folklorico argentino: historias, letras y contexto',
            'empty_heading' => 'Todavia no hay articulos publicados sobre cancionero',
            'empty_text' => 'Aca se iran sumando articulos sobre canciones fundamentales del repertorio folklorico argentino y su contexto cultural.',
        ],
        'aprender' => [
            'intro' => 'Esta landing esta orientada a usuarios con una intencion practica: aprender folklore argentino de forma clara y progresiva. Reunira guias para tocar, cantar, escuchar mejor y adquirir bases solidas para entrar al genero con criterio.',
            'list_heading' => 'Guias para aprender folklore argentino',
            'empty_heading' => 'Todavia no hay articulos publicados para aprender folklore argentino',
            'empty_text' => 'Esta seccion reunira contenidos practicos para iniciarse en guitarra, canto, escucha y acompanamiento dentro del folklore argentino.',
        ],
    ];

    public function index()
    {
        $categories = KnowledgeCategory::active()->withCount([
            'articles as published_articles_count' => fn ($query) => $query->visible(),
        ])->get();

        $featuredArticles = KnowledgeArticle::visible()
            ->with(['category', 'images'])
            ->latest('published_at')
            ->take(12)
            ->get();

        $metaTitle = 'Enciclopedia del folklore argentino';
        $metaDescription = 'Guías, historia, ritmos, instrumentos, provincias y tradiciones del folklore argentino en una enciclopedia editorial pensada para consulta permanente.';
        $breadcrumbs = [
            ['label' => 'Enciclopedia', 'url' => route('enciclopedia.index')],
        ];

        return view('frontend.knowledge.index', compact(
            'categories',
            'featuredArticles',
            'metaTitle',
            'metaDescription',
            'breadcrumbs'
        ));
    }

    public function category(string $categorySlug)
    {
        $category = KnowledgeCategory::active()
            ->where('slug', $categorySlug)
            ->firstOrFail();

        $articles = $category->articles()
            ->visible()
            ->with(['category', 'images'])
            ->paginate(12);

        $metaTitle = $category->seo_title ?: 'Enciclopedia del folklore argentino: '.$category->name;
        $metaDescription = $category->meta_description
            ?: Str::limit(strip_tags($category->description ?: 'Contenidos de referencia sobre '.strtolower($category->name).'.'), 160);
        $breadcrumbs = [
            ['label' => 'Enciclopedia', 'url' => route('enciclopedia.index')],
            ['label' => $category->name],
        ];
        $landingCopy = self::CATEGORY_LANDING_COPY[$category->slug] ?? [
            'intro' => $category->description,
            'list_heading' => 'Articulos de la enciclopedia sobre '.$category->name,
            'empty_heading' => 'Todavia no hay articulos publicados en esta familia',
            'empty_text' => 'Esta landing va a reunir contenidos editoriales de referencia sobre '.$category->name.'.',
        ];

        return view('frontend.knowledge.category', compact(
            'category',
            'articles',
            'landingCopy',
            'metaTitle',
            'metaDescription',
            'breadcrumbs'
        ));
    }

    public function show(string $categorySlug, string $articleSlug)
    {
        $article = KnowledgeArticle::visible()
            ->with([
                'category',
                'author',
                'images',
                'interpretes',
                'canciones.interprete',
                'albums.interprete',
                'festivales.provincia',
                'events.provincia',
                'provincias',
                'relatedArticles' => fn ($query) => $query->visible()->with('category'),
            ])
            ->whereHas('category', fn ($query) => $query->where('slug', $categorySlug)->where('is_active', true))
            ->where('slug', $articleSlug)
            ->firstOrFail();

        $article->increment('visits');

        $metaTitle = $article->seo_title ?: $article->title.' | Enciclopedia del folklore argentino';
        $metaDescription = $article->meta_description ?: Str::limit(strip_tags($article->excerpt ?: $article->body), 160);
        $canonical = CanonicalUrl::normalize(route('enciclopedia.show', [
            'categorySlug' => $article->category->slug,
            'articleSlug' => $article->slug,
        ]));
        $breadcrumbs = [
            ['label' => 'Enciclopedia', 'url' => route('enciclopedia.index')],
            ['label' => $article->category->name, 'url' => route('enciclopedia.category', $article->category->slug)],
            ['label' => $article->title],
        ];

        return view('frontend.knowledge.show', compact(
            'article',
            'metaTitle',
            'metaDescription',
            'canonical',
            'breadcrumbs'
        ));
    }
}
