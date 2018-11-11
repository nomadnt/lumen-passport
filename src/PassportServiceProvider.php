<?php namespace Nomadnt\LumenPassport;

use Laravel\Passport\PassportServiceProvider as ServiceProvider;

use Illuminate\Database\Connection;

class PassportServiceProvider extends ServiceProvider{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){
        parent::boot();

        $this->app->singleton(Connection::class, function() {
            return $this->app['db.connection'];
        });
    }
}
