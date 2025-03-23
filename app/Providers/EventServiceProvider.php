<?php

namespace App\Providers;
use App\Mail\NewAccountCreated;
use App\Mail\SendCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * map events to listeners.
     *
     * @var array
     */


    /**
     * register any events for your application.
     */
    public function boot()
    {
        parent::boot();

        Event::listen(Registered::class, function ($event) {
            Mail::to($event->user->email)->send(new SendCode($event->user));
        });
    }
}
