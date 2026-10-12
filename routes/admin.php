<?php

use App\Http\Controllers\Backend\AlbumController;
use App\Http\Controllers\Backend\CancionController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ClassifiedController;
use App\Http\Controllers\Backend\ComidaController;
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
use App\Http\Controllers\Backend\EntityClaimRequestController;
use App\Http\Controllers\Backend\MyEntitiesController;
use App\Http\Controllers\Backend\OrganizationController;
use App\Http\Controllers\Backend\OrganizationEntityController;
use App\Http\Controllers\Backend\OrganizationLookupController;
use App\Http\Controllers\Backend\OrganizationMemberController;
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
    Route::post('events/{event}/sessions', [EventController::class, 'addSessions'])->middleware('backoffice.section:eventos')->name('backend.events.sessions');
    Route::resource('events', EventController::class)->middleware('backoffice.section:eventos')->names('backend.events')->parameters(['events' => 'event']);
    Route::resource('venues', \App\Http\Controllers\Backend\VenueController::class)->except('show')->names('backend.venues');
    Route::resource('event-programs', \App\Http\Controllers\Backend\EventProgramController::class)->middleware('backoffice.section:eventos')->except(['show', 'destroy'])->names('backend.event-programs');
    Route::resource('news', NewsController::class)->middleware('backoffice.section:noticias')->names('backend.news')->parameters(['news' => 'news']);
    Route::middleware('backoffice.section:enciclopedia')->group(function () {
        Route::resource('knowledge-articles', KnowledgeArticleController::class)
            ->names('backend.knowledge-articles')
            ->parameters(['knowledge-articles' => 'knowledge_article']);
        Route::get('knowledge-articles/{knowledge_article}/preview', [KnowledgeArticleController::class, 'preview'])
            ->name('backend.knowledge-articles.preview');
        Route::post('knowledge-articles/{knowledge_article}/publish', [KnowledgeArticleController::class, 'publish'])
            ->name('backend.knowledge-articles.publish');
        Route::post('knowledge-articles/{knowledge_article}/unpublish', [KnowledgeArticleController::class, 'unpublish'])
            ->name('backend.knowledge-articles.unpublish');
    });
    Route::middleware('backoffice.section:copa')->group(function () {
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
    });

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

    // --- MIS FICHAS: cualquier usuario autenticado puede solicitar administrar una ficha ---
    Route::get('mis-fichas', [MyEntitiesController::class, 'index'])->name('backend.my-entities.index');
    Route::get('mis-fichas/buscar', [MyEntitiesController::class, 'search'])->name('backend.my-entities.search');
    Route::get('mis-fichas/similares', [MyEntitiesController::class, 'similar'])->name('backend.my-entities.similar');
    Route::post('mis-fichas/solicitar-alta', [MyEntitiesController::class, 'storeCreation'])->middleware('throttle:10,1')->name('backend.my-entities.creations.store');
    Route::post('mis-fichas/solicitar', [MyEntitiesController::class, 'store'])->middleware('throttle:10,1')->name('backend.my-entities.claims.store');
    Route::post('mis-fichas/revision/{claim}/aprobar', [MyEntitiesController::class, 'reviewApprove'])->middleware('throttle:30,1')->name('backend.my-entities.review.approve');
    Route::post('mis-fichas/revision/{claim}/rechazar', [MyEntitiesController::class, 'reviewReject'])->middleware('throttle:30,1')->name('backend.my-entities.review.reject');
    Route::post('mis-fichas/solicitudes/{claim}/cancelar', [MyEntitiesController::class, 'cancel'])->name('backend.my-entities.claims.cancel');

    // --- ORGANIZACIONES (administración de fichas, contacto y miembros) ---
    Route::middleware('role:administrador')->group(function () {
        Route::get('claims', [EntityClaimRequestController::class, 'index'])->name('backend.claims.index');
        Route::get('claims/{claim}', [EntityClaimRequestController::class, 'show'])->name('backend.claims.show');
        Route::post('claims/{claim}/approve', [EntityClaimRequestController::class, 'approve'])->name('backend.claims.approve');
        Route::post('claims/{claim}/reject', [EntityClaimRequestController::class, 'reject'])->name('backend.claims.reject');

        Route::get('organization-lookups/entities', [OrganizationLookupController::class, 'entities'])->name('backend.organization-lookups.entities');
        Route::get('organization-lookups/users', [OrganizationLookupController::class, 'users'])->name('backend.organization-lookups.users');

        Route::get('organizations/export', [OrganizationController::class, 'export'])->name('backend.organizations.export');

        Route::resource('organizations', OrganizationController::class)
            ->except(['show', 'destroy'])
            ->names('backend.organizations')
            ->parameters(['organizations' => 'organization']);

        Route::scopeBindings()->prefix('organizations/{organization}')->name('backend.organizations.')->group(function () {
            Route::post('contacted', [OrganizationController::class, 'markContacted'])->name('contacted');
            Route::post('entities', [OrganizationEntityController::class, 'store'])->name('entities.store');
            Route::patch('entities/{entityLink}/primary', [OrganizationEntityController::class, 'primary'])->name('entities.primary');
            Route::delete('entities/{entityLink}', [OrganizationEntityController::class, 'destroy'])->name('entities.destroy');
            Route::post('members', [OrganizationMemberController::class, 'store'])->name('members.store');
            Route::patch('members/{member}', [OrganizationMemberController::class, 'update'])->name('members.update');
            Route::delete('members/{member}', [OrganizationMemberController::class, 'destroy'])->name('members.destroy');
        });
    });
    Route::resource('interpretes', InterpreteController::class)->middleware('backoffice.section:interpretes')->names('backend.interpretes');
    Route::resource('categories', CategoryController::class)->names('backend.categories');
    Route::resource('tags', TagController::class)->names('backend.tags');

    // --- CLASIFICADOS ---
    Route::resource('classifieds', ClassifiedController::class)->names('backend.classifieds');
    Route::post('classifieds/{classified}/approve', [ClassifiedController::class, 'approve'])->name('backend.classifieds.approve');
    Route::post('classifieds/{classified}/reject', [ClassifiedController::class, 'reject'])->name('backend.classifieds.reject');

    // --- OTROS CONTENIDOS ---
    Route::resource('mitos', MitoController::class)->middleware('backoffice.section:mitos')->names('backend.mitos');
    Route::resource('comidas', ComidaController::class)->middleware('backoffice.section:comidas')->names('backend.comidas');
    Route::resource('festivales', FestivalController::class)->middleware('backoffice.section:festivales')->names('backend.festivales')->parameters(['festivales' => 'festival']);
    Route::middleware('backoffice.section:penas')->group(function () {
        Route::get('penia-profiles/{penia_profile}/preview', [PeniaProfileController::class, 'preview'])->name('backend.penia-profiles.preview');
        Route::post('penia-profiles/{penia_profile}/publish', [PeniaProfileController::class, 'publish'])->name('backend.penia-profiles.publish');
        Route::post('penia-profiles/{penia_profile}/unpublish', [PeniaProfileController::class, 'unpublish'])->name('backend.penia-profiles.unpublish');
        Route::resource('penia-profiles', PeniaProfileController::class)->names('backend.penia-profiles')->parameters(['penia-profiles' => 'penia_profile']);
    });
    Route::post('radios/signals/{radio_signal}/publish', [RadioSignalController::class, 'publish'])->name('backend.radios.signals.publish');
    Route::post('radios/signals/{radio_signal}/unpublish', [RadioSignalController::class, 'unpublish'])->name('backend.radios.signals.unpublish');
    Route::get('radios/signals/{radio_signal}/preview', [RadioSignalController::class, 'preview'])->name('backend.radios.signals.preview');
    Route::resource('radios/signals', RadioSignalController::class)->names('backend.radios.signals')->parameters(['signals' => 'radio_signal'])->except('show');
    Route::post('radios/programs/{radio_program}/publish', [RadioProgramController::class, 'publish'])->name('backend.radios.programs.publish');
    Route::post('radios/programs/{radio_program}/unpublish', [RadioProgramController::class, 'unpublish'])->name('backend.radios.programs.unpublish');
    Route::get('radios/programs/{radio_program}/preview', [RadioProgramController::class, 'preview'])->name('backend.radios.programs.preview');
    Route::resource('radios/programs', RadioProgramController::class)->names('backend.radios.programs')->parameters(['programs' => 'radio_program'])->except('show');
    Route::resource('discos', AlbumController::class)->middleware('backoffice.section:discos')->names('backend.discos')->parameters(['discos' => 'album']);
    Route::get('canciones/data', [CancionController::class, 'getCanciones'])->middleware('backoffice.section:canciones')->name('backend.canciones.get');
    Route::post('canciones/store-ajax', [CancionController::class, 'storeAjax'])->middleware('backoffice.section:canciones')->name('backend.canciones.store-ajax');
    Route::resource('canciones', CancionController::class)->middleware('backoffice.section:canciones')->names('backend.canciones')->parameters(['canciones' => 'cancion']);

    // --- NEWSLETTER Y MODERACIÓN ---
    Route::resource('newsletter-subscribers', NewsletterSubscriberController::class)->only(['index'])->names('backend.newsletter');
    Route::post('newsletter-subscribers/{subscriber}/toggle', [NewsletterSubscriberController::class, 'toggleStatus'])->name('backend.newsletter.toggle');
    Route::get('moderation', [ModerationController::class, 'index'])->name('backend.moderation.index');
    Route::post('moderation/{type}/{id}/publish', [ModerationController::class, 'publish'])->name('backend.moderation.publish');


});
