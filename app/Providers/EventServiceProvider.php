<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Session;
use App\Helpers\InterpreteHelper;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Guardo en SESSION un Array con los Interpretes administrados
        Event::listen(Authenticated::class, function ($event) {
            $user_id = $event->user->id;
            if($user_id !== 1){
              $interpretes = InterpreteHelper::getInterpretesByUserId($user_id);
              
            }
            $interpretes = array();
            Session::put('interpretes', $interpretes);
        });
    }
}
