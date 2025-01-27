<?php

namespace Nomadnt\LumenPassport;

use Laravel\Passport\PassportServiceProvider as LaravelPassportServiceProvider;
use Illuminate\Support\Facades\Route;

class PassportServiceProvider extends LaravelPassportServiceProvider
{
    /**
     * Register Passport's routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'as' => 'passport.',
            'prefix' => config('passport.path', 'oauth'),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/web.php');
        });
    }
} 