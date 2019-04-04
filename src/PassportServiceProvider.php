<?php

namespace Nomadnt\LumenPassport;

use Laravel\Passport\PassportServiceProvider as ServiceProvider;

use Illuminate\Database\Connection;

class PassportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Connection::class, function(){
            return $this->app['db.connection'];
        });

        if(preg_match('/5\.[678]\.\d+/', $this->app->version())){
            $this->app->singleton(\Illuminate\Hashing\HashManager::class, function ($app) {
                return new \Illuminate\Hashing\HashManager($app);
            });
        }

        if($this->app->runningInConsole()){
            $this->commands([Console\Commands\PurgeCommand::class]);
        }
    }
}
