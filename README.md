<p align="center"><img src="https://laravel.com/assets/img/components/logo-passport.svg"></p>

[![Total Downloads](https://poser.pugx.org/nomadnt/lumen-passport/downloads)](https://packagist.org/packages/nomadnt/lumen-passport)
[![Latest Stable Version](https://poser.pugx.org/nomadnt/lumen-passport/v/stable)](https://packagist.org/packages/nomadnt/lumen-passport)
[![License](https://poser.pugx.org/nomadnt/lumen-passport/license)](https://packagist.org/packages/nomadnt/lumen-passport)


# Lumen Passport

Lumen porting of Laravel Passport.
The idea come from https://github.com/dusterio/lumen-passport but try to make it transparent with original laravel passport

## Dependencies

* PHP >= 7.1.3
* Lumen >= 5.6

## Installation via Composer

First install Lumen if you don't have it yet:
```bash
$ composer create-project --prefer-dist laravel/lumen lumen-app
```

Then install Lumen Passport (it will fetch Laravel Passport along):

```bash
$ cd lumen-app
$ composer require nomadnt/lumen-passport
```

Or if you prefer, edit `composer.json` manually:

```json
{
    "require": {
        "nomadnt/lumen-passport": "^7.2"
    }
}
```

### Modify the bootstrap flow (```bootstrap/app.php``` file)

We need to enable both Laravel Passport provider and Lumen-specific provider:

```php
// Enable Facades
$app->withFacades();

// Enable Eloquent
$app->withEloquent();

// Enable auth middleware (shipped with Lumen)
$app->routeMiddleware([
    'auth'     => App\Http\Middleware\Authenticate::class,
    'throttle' => Nomadnt\LumenPassport\Middleware\ThrottleRequests::class
]);

// Finally register two service providers - original one and Lumen adapter
$app->register(Nomadnt\LumenPassport\PassportServiceProvider::class);
```

### Migrate and install Laravel Passport

```bash
# Create new tables for Passport
php artisan migrate

# Install encryption keys and other necessary stuff for Passport
php artisan passport:install
```

## Configuration

Edit config/auth.php to suit your needs. A simple example:

```php
return [
    'defaults' => ['guard' => 'api'],

    'guards' => [
        'api' => ['driver' => 'passport', 'provider' => 'users'],
    ],

    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => \App\User::class]
    ]
];
```

## Registering Routes

Next, you should call the LumenPassport::routes method within the boot method of your application (one of your service providers).
This method will register the routes necessary to issue access tokens and revoke access tokens, clients, and personal access tokens:

```php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;

use Nomadnt\LumenPassport\Passport;

class AuthServiceProvider extends ServiceProvider{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(){

    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot(){
        // register passport routes
        Passport::routes();

        // change the default token expiration
        Passport::tokensExpireIn(Carbon::now()->addDays(15));

        // change the default refresh token expiration
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
    }
}

```

## User model

Make sure your user model uses Passport's ```HasApiTokens``` trait, eg.:

```php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Passport\HasApiTokens;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    // rest of the model
}
```

## Access Token Events

If you want to revoke or purge tokens you have to create related Listeners and register 
on your `App\Http\Providers\EventServiceProvider` istead of using deprecated properties
`Passport::$revokeOtherTokens = true;` and `Passport::$pruneRevokedTokens = true;`

```php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider{

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Laravel\Passport\Events\AccessTokenCreated' => [
            'App\Listeners\RevokeOldTokens',
            'App\Listeners\PruneRevokedTokens',
        ]
    ];
}
```

### Revoke Old Tokens

```php
namespace App\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Token;

use NomadNT\LumenPassport\Passport;

class RevokeOldTokens{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(){
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderShipped  $event
     * @return void
     */
    public function handle(AccessTokenCreated $event){
        Token::where(function($query) use($event){
            $query->where('user_id', $event->userId);
            $query->where('id', '<>', $event->tokenId);
        })->update(['revoked' => true]);        
    }
}
```

### Prune Revoked Tokens

```php
namespace App\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Token;

use NomadNT\LumenPassport\Passport;

class PruneRevokedTokens{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(){
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AccessTokenCreated  $event
     * @return void
     */
    public function handle(AccessTokenCreated $event){
        Token::where(function($query) use($event){
            $query->where('user_id', $event->userId);
            $query->where('id', '<>', $event->tokenId);
            $query->where('revoked', true);
        })->delete();
    }
}
```
