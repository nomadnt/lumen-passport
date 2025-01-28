<?php

namespace Nomadnt\LumenPassport;

use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider as LaravelPassportServiceProvider;

class PassportServiceProvider extends LaravelPassportServiceProvider
{
    /**
     * Register the Passport routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (Passport::$registersRoutes) {
            $this->app->router->group([
                'as' => 'passport.',
                'prefix' => config('passport.path', 'oauth'),
                'namespace' => 'Laravel\Passport\Http\Controllers',
            ], function ($router) {
                require __DIR__.'/../routes/web.php';
            });
        }
    }

    /**
    * Register the service provider.
    *
    * @return void
    */
    public function register()
    {
        parent::register();
        // This binding is necessary for Passport to work properly with Lumen, as it tells Lumen's container which implementation to use for the ResponseFactory contract.
        $this->app->singleton(
            \Illuminate\Contracts\Routing\ResponseFactory::class,
            \Laravel\Lumen\Http\ResponseFactory::class
        );
    }
} 