<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Paginator::useBootstrapFive();

        $this->invalidateResponseCacheOnContentChanges();

        // Gate::define('access-noticia', function (User $user) {
        //     // Verificar si el usuario tiene el permiso usando el paquete Spatie
        //     return $user->hasPermissionTo('create noticia');
        // });

        // Gate::define('access-user', function (User $user) {
        //     return $user->hasPermissionTo('access user');
        // });
    }

    /**
     * Con el cache de pagina completa (ver
     * App\Support\ResponseCache\PublicPagesCacheProfile) una noticia/ficha
     * editada no se veria reflejada hasta que venza el TTL del cache. Como no
     * tenemos Redis (sin tags de cache finos), la estrategia simple y
     * correcta a esta escala es: cualquier alta/edicion/baja de contenido
     * publico limpia TODO el cache de paginas. El proximo visitante regenera
     * solo lo que pide.
     */
    private function invalidateResponseCacheOnContentChanges(): void
    {
        if (! class_exists(\Spatie\ResponseCache\Facades\ResponseCache::class)) {
            return;
        }

        $models = [
            \App\Models\News::class,
            \App\Models\Interprete::class,
            \App\Models\Cancion::class,
            \App\Models\Album::class,
            \App\Models\Festival::class,
            \App\Models\Mito::class,
            \App\Models\Comida::class,
            \App\Models\Radio::class,
            \App\Models\PeniaProfile::class,
            \App\Models\Event::class,
        ];

        $clear = function () {
            \Spatie\ResponseCache\Facades\ResponseCache::clear();
        };

        foreach ($models as $model) {
            if (! class_exists($model)) {
                continue;
            }

            $model::saved($clear);
            $model::deleted($clear);
        }
    }
}
