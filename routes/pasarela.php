<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pasarela de Contenidos — Panel del Publicador
|--------------------------------------------------------------------------
| Rutas administrativas para la gestión de cuentas sociales, solicitudes de
| publicación multicanal, moderación y dashboard de publicador.
*/

Route::middleware(['web', 'auth'])->group(function () {

    // Raíz de la pasarela - Renombramos a 'index' para evitar colisión con el dashboard
    Route::get('/', [\App\Http\Controllers\Backend\PublisherDashboardController::class, 'index'])
        ->name('index');

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

    // PC-12-HU-01: Notificaciones
    Route::get('notificaciones', [\App\Http\Controllers\Pasarela\NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('notificaciones/{notification}/leida', [\App\Http\Controllers\Pasarela\NotificationController::class, 'markRead'])
        ->name('notifications.mark-read');
    Route::post('notificaciones/leidas-todas', [\App\Http\Controllers\Pasarela\NotificationController::class, 'markAllRead'])
        ->name('notifications.mark-all-read');
    Route::get('notificaciones/count', [\App\Http\Controllers\Pasarela\NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count');

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
