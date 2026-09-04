<?php

use App\Http\Controllers\Backend\AlbumController;
use App\Http\Controllers\Backend\CancionController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ClassifiedController;
use App\Http\Controllers\Backend\ComidaController;
use App\Http\Controllers\Backend\ContributionController;
use App\Http\Controllers\Backend\Dashboard;
use App\Http\Controllers\Backend\EventController;
use App\Http\Controllers\Backend\FestivalController;
use App\Http\Controllers\Backend\FolkloreTournamentController;
use App\Http\Controllers\Backend\FolkloreTournamentMatchController;
use App\Http\Controllers\Backend\InterpreteController;
use App\Http\Controllers\Backend\KnowledgeArticleController;
use App\Http\Controllers\Backend\MitoController;
use App\Http\Controllers\Backend\ModerationController;
use App\Http\Controllers\Backend\NewsController;
use App\Http\Controllers\Backend\NewsletterSubscriberController;
use App\Http\Controllers\Backend\PeniaProfileController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\PublisherDashboardController;
use App\Http\Controllers\Backend\RadioProgramController;
use App\Http\Controllers\Backend\RadioSignalController;
use App\Http\Controllers\Backend\RoleController;
// Nuevos controladores alineados con la nomenclatura Events/News
use App\Http\Controllers\Backend\TagController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\OperationalHealthController;
// Controladores de la Pasarela
use App\Http\Controllers\Pasarela\DashboardAdminController;
use App\Http\Controllers\Pasarela\DashboardPublicadorController;
use App\Http\Controllers\Pasarela\NotificationController;
use App\Http\Controllers\Pasarela\PublicationRequestController;
use App\Http\Controllers\Pasarela\PublicationTemplateController;
use App\Http\Controllers\Pasarela\SocialAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // --- DASHBOARD PRINCIPAL ---
    Route::get('/', [Dashboard::class, 'index'])->name('dashboard');
    Route::get('operational-health', [OperationalHealthController::class, 'diagnosis'])->middleware('role:administrador')->name('backend.operational-health');

    // --- GESTIÓN DE CONTENIDOS (EVENTOS Y NOTICIAS) ---
    Route::resource('events', EventController::class)->names('backend.events')->parameters(['events' => 'event']);
    Route::resource('news', NewsController::class)->names('backend.news')->parameters(['news' => 'news']);
    Route::resource('knowledge-articles', KnowledgeArticleController::class)
        ->names('backend.knowledge-articles')
        ->parameters(['knowledge-articles' => 'knowledge_article']);
    Route::get('knowledge-articles/{knowledge_article}/preview', [KnowledgeArticleController::class, 'preview'])
        ->name('backend.knowledge-articles.preview');
    Route::post('knowledge-articles/{knowledge_article}/publish', [KnowledgeArticleController::class, 'publish'])
        ->name('backend.knowledge-articles.publish');
    Route::post('knowledge-articles/{knowledge_article}/unpublish', [KnowledgeArticleController::class, 'unpublish'])
        ->name('backend.knowledge-articles.unpublish');
    Route::resource('folklore-tournaments', FolkloreTournamentController::class)
        ->only(['index', 'show', 'edit', 'update'])
        ->names('backend.folklore-tournaments')
        ->parameters(['folklore-tournaments' => 'tournament']);
    Route::get('folklore-tournaments/{tournament}/matches', [FolkloreTournamentController::class, 'matches'])
        ->name('backend.folklore-tournaments.matches');
    Route::get('folklore-tournament-matches/{match}/edit', [FolkloreTournamentMatchController::class, 'edit'])
        ->name('backend.folklore-tournament-matches.edit');
    Route::put('folklore-tournament-matches/{match}', [FolkloreTournamentMatchController::class, 'update'])
        ->name('backend.folklore-tournament-matches.update');

    // --- PASARELA DE CONTENIDOS (REDES SOCIALES Y PUBLICACIÓN) ---
    Route::prefix('pasarela')->name('pasarela.')->group(function () {
        Route::get('/', [PublisherDashboardController::class, 'index'])->name('index');
        Route::get('dashboard', [DashboardPublicadorController::class, 'index'])->name('dashboard');
        Route::get('admin/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard');

        Route::resource('social-accounts', SocialAccountController::class)->only(['index', 'store', 'destroy'])->names('social-accounts');
        Route::resource('publication-requests', PublicationRequestController::class)->only(['index', 'create', 'store', 'show'])->names('publication-requests');
        Route::post('publication-requests/{publicationRequest}/targets/{target}/retry', [PublicationRequestController::class, 'retryTarget'])->name('publication-requests.targets.retry');

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('{notification}/read', [NotificationController::class, 'markRead'])->name('mark-read');
            Route::post('read-all', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
            Route::get('count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        });

        Route::resource('templates', PublicationTemplateController::class)->names('templates');
        Route::post('templates/preview', [PublicationTemplateController::class, 'preview'])->name('templates.preview');
    });

    // --- SEGURIDAD Y ORGANIZACIÓN ---
    Route::resource('roles', RoleController::class)->names('roles');
    Route::resource('users', UserController::class)->names('users');
    Route::post('users/{user}/api-tokens', [UserController::class, 'issueApiToken'])->name('users.api-tokens.store');
    Route::delete('users/{user}/api-tokens/{token}', [UserController::class, 'revokeApiToken'])->name('users.api-tokens.destroy');
    Route::resource('permissions', PermissionController::class)->names('permissions');
    Route::resource('interpretes', InterpreteController::class)->names('backend.interpretes');
    Route::resource('categories', CategoryController::class)->names('backend.categories');
    Route::resource('tags', TagController::class)->names('backend.tags');

    // --- CLASIFICADOS ---
    Route::resource('classifieds', ClassifiedController::class)->names('backend.classifieds');
    Route::post('classifieds/{classified}/approve', [ClassifiedController::class, 'approve'])->name('backend.classifieds.approve');
    Route::post('classifieds/{classified}/reject', [ClassifiedController::class, 'reject'])->name('backend.classifieds.reject');

    // --- OTROS CONTENIDOS ---
    Route::resource('mitos', MitoController::class)->names('backend.mitos');
    Route::resource('comidas', ComidaController::class)->names('backend.comidas');
    Route::resource('festivales', FestivalController::class)->names('backend.festivales')->parameters(['festivales' => 'festival']);
    Route::get('penia-profiles/{penia_profile}/preview', [PeniaProfileController::class, 'preview'])->name('backend.penia-profiles.preview');
    Route::post('penia-profiles/{penia_profile}/publish', [PeniaProfileController::class, 'publish'])->name('backend.penia-profiles.publish');
    Route::post('penia-profiles/{penia_profile}/unpublish', [PeniaProfileController::class, 'unpublish'])->name('backend.penia-profiles.unpublish');
    Route::resource('penia-profiles', PeniaProfileController::class)->names('backend.penia-profiles')->parameters(['penia-profiles' => 'penia_profile']);
    Route::post('radios/signals/{radio_signal}/publish', [RadioSignalController::class, 'publish'])->name('backend.radios.signals.publish');
    Route::post('radios/signals/{radio_signal}/unpublish', [RadioSignalController::class, 'unpublish'])->name('backend.radios.signals.unpublish');
    Route::get('radios/signals/{radio_signal}/preview', [RadioSignalController::class, 'preview'])->name('backend.radios.signals.preview');
    Route::resource('radios/signals', RadioSignalController::class)->names('backend.radios.signals')->parameters(['signals' => 'radio_signal'])->except('show');
    Route::post('radios/programs/{radio_program}/publish', [RadioProgramController::class, 'publish'])->name('backend.radios.programs.publish');
    Route::post('radios/programs/{radio_program}/unpublish', [RadioProgramController::class, 'unpublish'])->name('backend.radios.programs.unpublish');
    Route::get('radios/programs/{radio_program}/preview', [RadioProgramController::class, 'preview'])->name('backend.radios.programs.preview');
    Route::resource('radios/programs', RadioProgramController::class)->names('backend.radios.programs')->parameters(['programs' => 'radio_program'])->except('show');
    Route::resource('discos', AlbumController::class)->names('backend.discos')->parameters(['discos' => 'album']);
    Route::get('canciones/data', [CancionController::class, 'getCanciones'])->name('backend.canciones.get');
    Route::post('canciones/store-ajax', [CancionController::class, 'storeAjax'])->name('backend.canciones.store-ajax');
    Route::resource('canciones', CancionController::class)->names('backend.canciones')->parameters(['canciones' => 'cancion']);

    // --- NEWSLETTER Y MODERACIÓN ---
    Route::resource('newsletter-subscribers', NewsletterSubscriberController::class)->only(['index'])->names('backend.newsletter');
    Route::post('newsletter-subscribers/{subscriber}/toggle', [NewsletterSubscriberController::class, 'toggleStatus'])->name('backend.newsletter.toggle');
    Route::get('moderation', [ModerationController::class, 'index'])->name('backend.moderation.index');
    Route::post('moderation/{type}/{id}/publish', [ModerationController::class, 'publish'])->name('backend.moderation.publish');

    // --- MODERACIÓN DE CONTRIBUCIONES UGC ---
    Route::prefix('contributions')->name('backend.contributions.')->group(function () {
        Route::get('/', [ContributionController::class, 'index'])->name('admin.index');
        Route::get('{id}', [ContributionController::class, 'show'])->name('show');
        Route::post('{id}/approve', [ContributionController::class, 'approve'])->name('approve');
        Route::post('{id}/reject', [ContributionController::class, 'reject'])->name('reject');
    });

});
