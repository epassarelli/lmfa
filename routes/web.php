<?php

use Illuminate\Support\Facades\Route;

// Controladores del front
use App\Http\Controllers\Frontend\NoticiasController;
use App\Http\Controllers\Frontend\CancionesController;
use App\Http\Controllers\Frontend\DiscosController;
use App\Http\Controllers\Frontend\EntrevistasController;
use App\Http\Controllers\Frontend\FestivalesController;
use App\Http\Controllers\Frontend\InterpretesController;
use App\Http\Controllers\Frontend\MitosController;
use App\Http\Controllers\Frontend\PeniasController;
use App\Http\Controllers\Frontend\RadiosController;
use App\Http\Controllers\Frontend\RecetasController;
use App\Http\Controllers\Frontend\ShowsController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ContactoController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Frontend\BusquedaController;
use App\Http\Controllers\Frontend\CompartirController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Frontend\ContributionController;
use App\Http\Controllers\Frontend\ClassifiedsController;

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemap-news.xml', [SitemapController::class, 'newsIndex']);

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Secciones generales (con slugs largos ya posicionados)
Route::get('/noticias-del-folklore-argentino', [NoticiasController::class, 'index'])->name('noticias.index');
Route::get('/noticias-del-folklore-argentino/{slug}', [NoticiasController::class, 'generalShow'])->name('noticias.show');

Route::get('/cartelera-de-eventos-folkloricos', [ShowsController::class, 'index'])->name('cartelera.index');
Route::get('/cartelera-de-eventos-folkloricos/{slug}', [ShowsController::class, 'showGeneral'])->name('cartelera.show');

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

// Colaboraciones (UGC) - Mover ARRIBA para evitar colisión con slugs de artistas
Route::middleware(['auth'])->prefix('colaborar')->group(function () {
    Route::get('/', [ContributionController::class, 'index'])->name('contributions.index');
    Route::get('/{type}/{id?}', [ContributionController::class, 'create'])->name('contributions.create');
    Route::post('/store', [ContributionController::class, 'store'])->name('contributions.store');
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
  Route::get('/shows/{slug}', [ShowsController::class, 'show'])->name('artista.showdetalle');

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

// =============================================================================
// Pasarela de Contenidos — Panel del Publicador
// PC-06-HU-01: Cuentas sociales
// PC-07-HU-01: Publication requests
// PC-10-HU-01: Dashboard publicador
// =============================================================================
Route::middleware(['auth'])->prefix('pasarela')->name('pasarela.')->group(function () {

    // Dashboard publicador
    Route::get('/', [\App\Http\Controllers\Backend\PublisherDashboardController::class, 'index'])
        ->name('dashboard');

    // Cuentas sociales: listar, conectar, desconectar
    Route::get('cuentas-sociales', [\App\Http\Controllers\Pasarela\SocialAccountController::class, 'index'])
        ->name('social-accounts.index');
    Route::post('cuentas-sociales', [\App\Http\Controllers\Pasarela\SocialAccountController::class, 'store'])
        ->name('social-accounts.store');
    Route::delete('cuentas-sociales/{socialAccount}', [\App\Http\Controllers\Pasarela\SocialAccountController::class, 'destroy'])
        ->name('social-accounts.destroy');

    // PC-07-HU-01: Solicitudes de publicación multicanal
    Route::get('publicaciones', [\App\Http\Controllers\Pasarela\PublicationRequestController::class, 'index'])
        ->name('publication-requests.index');
    Route::get('publicaciones/nueva', [\App\Http\Controllers\Pasarela\PublicationRequestController::class, 'create'])
        ->name('publication-requests.create');
    Route::post('publicaciones', [\App\Http\Controllers\Pasarela\PublicationRequestController::class, 'store'])
        ->name('publication-requests.store');
    Route::get('publicaciones/{publicationRequest}', [\App\Http\Controllers\Pasarela\PublicationRequestController::class, 'show'])
        ->name('publication-requests.show');

    // PC-10-HU-01: Dashboard publicador
    Route::get('dashboard', [\App\Http\Controllers\Pasarela\DashboardPublicadorController::class, 'index'])
        ->name('dashboard');

    // PC-11-HU-01: Dashboard admin
    Route::get('admin/dashboard', [\App\Http\Controllers\Pasarela\DashboardAdminController::class, 'index'])
        ->name('admin.dashboard');

    // PC-08-HU-01: Templates por canal
    Route::get('admin/templates', [\App\Http\Controllers\Pasarela\PublicationTemplateController::class, 'index'])
        ->name('templates.index');
    Route::get('admin/templates/nuevo', [\App\Http\Controllers\Pasarela\PublicationTemplateController::class, 'create'])
        ->name('templates.create');
    Route::post('admin/templates', [\App\Http\Controllers\Pasarela\PublicationTemplateController::class, 'store'])
        ->name('templates.store');
    Route::get('admin/templates/{template}', [\App\Http\Controllers\Pasarela\PublicationTemplateController::class, 'edit'])
        ->name('templates.edit');
    Route::put('admin/templates/{template}', [\App\Http\Controllers\Pasarela\PublicationTemplateController::class, 'update'])
        ->name('templates.update');
    Route::delete('admin/templates/{template}', [\App\Http\Controllers\Pasarela\PublicationTemplateController::class, 'destroy'])
        ->name('templates.destroy');
    Route::post('admin/templates/preview', [\App\Http\Controllers\Pasarela\PublicationTemplateController::class, 'preview'])
        ->name('templates.preview');
});
