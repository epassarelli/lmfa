<?php

use App\Http\Controllers\Frontend\NoticiasController;
// Controladores del front
use Illuminate\Support\Facades\Route;

// --- PUBLICAR / CONTRIBUCIONES ---
// Panel del colaborador (usuario autenticado ve sus propias contribuciones)
Route::middleware(['web', 'auth'])->prefix('admin/contribuir')->group(function () {
    Route::get('/', [\App\Http\Controllers\Frontend\ContributionController::class, 'index'])->name('backend.contributions.index');
    Route::get('crear/{type}/{id?}', [\App\Http\Controllers\Frontend\ContributionController::class, 'create'])->name('backend.contributions.create');
    Route::post('store', [\App\Http\Controllers\Frontend\ContributionController::class, 'store'])->name('backend.contributions.store');
});
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Frontend\BusquedaController;
use App\Http\Controllers\Frontend\CancionesController;
use App\Http\Controllers\Frontend\ClassifiedsController;
use App\Http\Controllers\Frontend\CompartirController;
use App\Http\Controllers\Frontend\ContactoController;
use App\Http\Controllers\Frontend\DiscosController;
use App\Http\Controllers\Frontend\EntrevistasController;
use App\Http\Controllers\Frontend\FestivalesController;
use App\Http\Controllers\Frontend\FolkloreTournamentController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\InterpretesController;
use App\Http\Controllers\Frontend\KnowledgeController;
use App\Http\Controllers\Frontend\MitosController;
use App\Http\Controllers\Frontend\PeniasController;
use App\Http\Controllers\Frontend\RadiosController;
use App\Http\Controllers\Frontend\RecetasController;
use App\Http\Controllers\Frontend\ShowsController;
use App\Http\Controllers\Frontend\SitemapController;

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-estaticas.xml', [SitemapController::class, 'staticPages'])->name('sitemap.static');
Route::get('/sitemap-artistas.xml', [SitemapController::class, 'artists'])->name('sitemap.artists');
Route::get('/sitemap-biografias.xml', [SitemapController::class, 'biographies'])->name('sitemap.biographies');
Route::get('/sitemap-noticias.xml', [SitemapController::class, 'news'])->name('sitemap.news');
Route::get('/sitemap-google-news.xml', [SitemapController::class, 'googleNews'])->name('sitemap.google-news');
Route::get('/sitemap-eventos.xml', [SitemapController::class, 'events'])->name('sitemap.events');
Route::get('/sitemap-festivales.xml', [SitemapController::class, 'festivals'])->name('sitemap.festivals');
Route::get('/sitemap-discografias.xml', [SitemapController::class, 'discographies'])->name('sitemap.discographies');
Route::get('/sitemap-letras.xml', [SitemapController::class, 'lyrics'])->name('sitemap.lyrics');
Route::get('/sitemap-evergreen.xml', [SitemapController::class, 'evergreen'])->name('sitemap.evergreen');
Route::get('/sitemap-main.xml', [SitemapController::class, 'legacyMain'])->name('sitemap.legacy-main');
Route::get('/sitemap-news.xml', [SitemapController::class, 'legacyGoogleNews'])->name('sitemap.legacy-news');

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Secciones generales (con slugs largos ya posicionados)
Route::get('/noticias-del-folklore-argentino', [NoticiasController::class, 'index'])->name('noticias.index');
Route::get('/noticias-del-folklore-argentino/{slug}', [NoticiasController::class, 'show'])->name('noticias.show');

Route::get('/cartelera-de-eventos-folkloricos', [ShowsController::class, 'index'])->name('cartelera.index');
Route::get('/cartelera-de-eventos-folkloricos/{provinceOrSlug}/{period?}', [ShowsController::class, 'resolve'])
    ->where('period', 'hoy|[a-z0-9\-]+')
    ->name('cartelera.show');

Route::get('/biografias-de-artistas-folkloricos', [InterpretesController::class, 'index'])->name('interpretes.index');
Route::get('/biografias-de-artistas-folkloricos/letra/{letra}', [InterpretesController::class, 'letra'])->name('interpretes.letra');
Route::get('/letras-de-canciones-folkloricas', [CancionesController::class, 'index'])->name('canciones.index');
Route::get('/letras-de-canciones-folkloricas/letra/{letra}', [CancionesController::class, 'letra'])->name('canciones.letra');
Route::get('/discografias-del-folklore-argentino', [DiscosController::class, 'index'])->name('discografias.index');

Route::get('/festivales-y-fiestas-tradicionales', [FestivalesController::class, 'index'])->name('festivales.index');
Route::get('/festivales-y-fiestas-tradicionales/{slug}', [FestivalesController::class, 'show'])->name('festivales.show');

Route::get('/radios-de-folklore-argentino', [RadiosController::class, 'index'])->name('radios.index');
Route::get('/radios-de-folklore-argentino/{slug}', [RadiosController::class, 'show'])->name('radios.show');

Route::get('/penias-folkloricas-de-argentina', [PeniasController::class, 'index'])->name('penias.index');
Route::get('/penias-folkloricas-de-argentina/{slug}', [PeniasController::class, 'show'])->name('penias.show');

Route::get('/mitos-y-leyendas-argentinas', [MitosController::class, 'index'])->name('mitos.index');
Route::get('/mitos-y-leyendas-argentinas/letra/{slug}', [MitosController::class, 'letra'])->name('mitos.letra');
Route::get('/mitos-y-leyendas-argentinas/{slug}', [MitosController::class, 'show'])->name('mitos.show');

Route::get('/recetas-de-comidas-tipicas-argentinas', [RecetasController::class, 'index'])->name('comidas.index');
Route::get('/recetas-de-comidas-tipicas-argentinas/letra/{slug}', [RecetasController::class, 'letra'])->name('comidas.letra');
Route::get('/recetas-de-comidas-tipicas-argentinas/{slug}', [RecetasController::class, 'show'])->name('comidas.show');

Route::prefix('copa-del-folklore-argentino-2026')->name('folklore.cup.')->group(function () {
    Route::get('/', [FolkloreTournamentController::class, 'index'])->name('index');
    Route::get('participantes', [FolkloreTournamentController::class, 'participants'])->name('participants');
    Route::get('fixture', [FolkloreTournamentController::class, 'fixture'])->name('fixture');
    Route::get('zonas', [FolkloreTournamentController::class, 'groups'])->name('groups');
    Route::get('llaves', [FolkloreTournamentController::class, 'bracket'])->name('bracket');
    Route::get('reglamento', [FolkloreTournamentController::class, 'rules'])->name('rules');
});

Route::prefix('enciclopedia')->name('enciclopedia.')->group(function () {
    Route::get('/', [KnowledgeController::class, 'index'])->name('index');
    Route::get('{categorySlug}', [KnowledgeController::class, 'category'])->name('category');
    Route::get('{categorySlug}/{articleSlug}', [KnowledgeController::class, 'show'])->name('show');
});

// Contacto
Route::get('/contacto', [ContactoController::class, 'index'])->name('contacto');
Route::post('/contacto', [ContactoController::class, 'store'])->name('contacto.store');

// Buscador y compartir
Route::get('/buscar', [BusquedaController::class, 'index'])->name('buscar');
Route::post('/compartir', [CompartirController::class, 'store'])->name('compartir.store');

// Clasificados
Route::prefix('avisos-clasificados')->name('classifieds.')->group(function () {
    Route::get('/', [ClassifiedsController::class, 'index'])->name('index');
    Route::get('/publicar', [ClassifiedsController::class, 'create'])->name('create')->middleware('auth');
    Route::post('/publicar', [ClassifiedsController::class, 'store'])->name('store')->middleware('auth');
    Route::get('/mis-avisos', [ClassifiedsController::class, 'misAvisos'])->name('mis-avisos')->middleware('auth');
    Route::post('/renovar/{classified}', [ClassifiedsController::class, 'renovar'])->name('renovar')->middleware('auth');
    Route::get('/{classified:slug}', [ClassifiedsController::class, 'show'])->name('show');
});

// Miniportal del artista y secciones internas
Route::get('/{interprete:slug}', [InterpretesController::class, 'show'])->name('artista.show');

Route::prefix('{interprete:slug}')->group(function () {
    Route::get('/biografia', [InterpretesController::class, 'biografia'])->name('artista.biografia');

    Route::get('/noticias', [NoticiasController::class, 'byArtista'])->name('artista.noticias');
    Route::get('/noticias/{noticia:slug}', [NoticiasController::class, 'show'])->name('artista.noticia');

    Route::get('/letras', [CancionesController::class, 'byArtista'])->name('artista.canciones');
    Route::get('/letras/{cancion:slug}', [CancionesController::class, 'show'])->name('artista.cancion');

    Route::get('/discografia', [DiscosController::class, 'byArtista'])->name('artista.discografia');
    Route::get('/discografia/{slug}', [DiscosController::class, 'show'])->name('artista.disco');

    Route::get('/shows', [ShowsController::class, 'byArtista'])->name('artista.shows');
    Route::get('/shows/{slug}', [ShowsController::class, 'show'])->name('artista.show.detalle');

    Route::get('/entrevistas', [EntrevistasController::class, 'byArtista'])->name('artista.entrevistas');
    Route::get('/entrevistas/{slug}', [EntrevistasController::class, 'show'])->name('artista.entrevista');
});

// Social Auth
Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
Route::get('auth/facebook', [SocialiteController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('auth/facebook/callback', [SocialiteController::class, 'handleFacebookCallback']);

// Newsletter
Route::post('/newsletter/subscribe', [\App\Http\Controllers\Frontend\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [\App\Http\Controllers\Frontend\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Auth::routes();
