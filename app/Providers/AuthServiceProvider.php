<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Event;
use App\Models\News;
use App\Policies\EventPolicy;
use App\Policies\NewsPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Event::class                  => EventPolicy::class,
        News::class                   => NewsPolicy::class,
        \App\Models\Interprete::class => \App\Policies\InterpretePolicy::class,
        \App\Models\Album::class      => \App\Policies\AlbumPolicy::class,
        \App\Models\Cancion::class    => \App\Policies\CancionPolicy::class,
        \App\Models\Festival::class   => \App\Policies\FestivalPolicy::class,
        \App\Models\Mito::class       => \App\Policies\MitoPolicy::class,
        \App\Models\Comida::class     => \App\Policies\ComidaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Bypass para el rol administrador
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('administrador') ? true : null;
        });
    }
}
