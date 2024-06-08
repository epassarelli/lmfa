<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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

        // Gate::define('access-noticia', function (User $user) {
        //     // Verificar si el usuario tiene el permiso usando el paquete Spatie
        //     return $user->hasPermissionTo('create noticia');
        // });

        // Gate::define('access-user', function (User $user) {
        //     return $user->hasPermissionTo('access user');
        // });
    }
}
