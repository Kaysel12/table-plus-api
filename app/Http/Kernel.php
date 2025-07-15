<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends HttpKernel
{
    /**
     * Middleware global que se ejecuta en todas las peticiones.
     */
    protected $middleware = [

        // Valida el tamaño de las peticiones POST
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,

        // Convierte cadenas vacías en null
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,

        // Middleware personalizado para forzar JSON
        \App\Http\Middleware\ForceJsonResponse::class,
    ];

    /**
     * Grupos de middleware.
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \App\Http\Middleware\ForceJsonResponse::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Middleware individual que puedes asignar a rutas.
     */
    protected $routeMiddleware = [
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('tasks:send-reminders')->everyMinute();
    }
}
