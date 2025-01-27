<?php

namespace Nomadnt\LumenPassport;

use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider as LaravelPassportServiceProvider;

class PassportServiceProvider extends LaravelPassportServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Remove the routes registration as they should be done in a different way
        $this->registerResources();
        $this->registerPublishing();
        $this->registerCommands();

        $this->deleteCookieOnLogout();
    }
} 