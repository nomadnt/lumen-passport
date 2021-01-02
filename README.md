<p align="center"><img src="https://laravel.com/assets/img/components/logo-passport.svg"></p>

[![Total Downloads](https://poser.pugx.org/nomadnt/lumen-passport/downloads)](https://packagist.org/packages/nomadnt/lumen-passport)
[![Latest Stable Version](https://poser.pugx.org/nomadnt/lumen-passport/v/stable)](https://packagist.org/packages/nomadnt/lumen-passport)
[![License](https://poser.pugx.org/nomadnt/lumen-passport/license)](https://packagist.org/packages/nomadnt/lumen-passport)


# Lumen Passport

Lumen porting of Laravel Passport.
The idea come from https://github.com/dusterio/lumen-passport but try to make it transparent with original laravel passport

## Dependencies

* PHP >= 7.3.0
* Lumen >= 8.0

## Installation

First of all let's install Lumen Framework if you haven't already.

```sh
composer create-project --prefer-dist laravel/lumen lumen-app && cd lumen-app
```

Then install Lumen Passport (it will fetch Laravel Passport along):

```sh
composer require nomadnt/lumen-passport
```

## Configuration

Generate your APP_KEY and update .env with single command

```sh
sed -i "s|\(APP_KEY=\)\(.*\)|\1$(openssl rand -base64 24)|" .env
```

Configure your database connection (ie to use SQLite)
This is how your .env file should looking after the changes

```env
APP_NAME=Lumen
APP_ENV=local
APP_KEY=<my-super-strong-api-key>
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_SLACK_WEBHOOK_URL=

DB_CONNECTION=sqlite

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

Copy the Lumen configuration folder to your project

```sh
cp -a vendor/laravel/lumen-framework/config config
```

Update `guards` and `provider` section of your config/auth.php to match Passport requirements

```php
<?php

return [
    ...

    'guards' => [
        'api' => ['driver' => 'passport', 'provider' => 'users']
    ],

    ...

    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => \App\Models\User::class]
    ]

    ...
];
```

You need to change a little the `bootstrap/app.php` file doing the following:

```php
<?php

...

// enable facades
$app->withFacades();

// enable eloquent
$app->withEloquent();

...

$app->configure('app');

// initialize auth configuration
$app->configure('auth');

...

// enable auth and throttle middleware
$app->routeMiddleware([
    'auth'     => App\Http\Middleware\Authenticate::class,
    'throttle' => Nomadnt\LumenPassport\Middleware\ThrottleRequests::class
]);

...

// register required service providers

// $app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Laravel\Passport\PassportServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

...
```

Create database.sqlite

```sh
touch database/database.sqlite
```

Lauch the migrations

```sh
php artisan migrate
```

Install Laravel passport

```sh
# Install encryption keys and other necessary stuff for Passport
php artisan passport:install
```

The previous command should give back to you an output similar to this:

```sh
Encryption keys generated successfully.
Personal access client created successfully.
Client ID: 1
Client secret: BxSueZnqimNTE0r98a0Egysq0qnonwkWDUl0KmE5
Password grant client created successfully.
Client ID: 2
Client secret: VFWuiJXTJhjb46Y04llOQqSd3kP3goqDLvVIkcIu
```

## Registering Routes

Now is time to register the passport routes necessary to issue access tokens and revoke access tokens, clients, and personal access tokens.  
To do this open you `app/Providers/AuthServiceProvider.php` and change the `boot` function to reflect the example below.

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;

// don't forget to include Passport
use Nomadnt\LumenPassport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
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

Make sure your user model uses Passport's `HasApiTokens` trait, eg.:

```php
<?php

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

### Prune and/or Revoke tokens

If you want to revoke or purge tokens on event based you have to create related Listeners and 
register on your `app/Http/Providers/EventServiceProvider.php` istead of using deprecated properties
`Passport::$revokeOtherTokens = true;` and `Passport::$pruneRevokedTokens = true;`

First you need to make sure that `EventServiceProvider` is registered on your `bootstrap/app.php`

```php
<?php

...

// $app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

...
```

Then you need to listen for `AccessTokenCreated` event and register your required listeners

```php
<?php

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
            'App\Listeners\RevokeOtherTokens',
            'App\Listeners\PruneRevokedTokens',
        ]
    ];
}
```

Create the `app/Listeners/RevokeOtherTokens.php` file and put the following content

```php
<?php

namespace App\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Token;

class RevokeOtherTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderShipped  $event
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        Token::where(function($query) use($event){
            $query->where('user_id', $event->userId);
            $query->where('id', '<>', $event->tokenId);
        })->revoke();
    }
}
```

Create the `app/Listeners/PruneRevokedTokens.php` file and put the following content

```php
<?php

namespace App\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Token;

class PruneRevokedTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AccessTokenCreated  $event
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        Token::where(function($query) use($event){
            $query->where('user_id', $event->userId);
            $query->where('id', '<>', $event->tokenId);
            $query->where('revoked', true);
        })->delete();
    }
}
```