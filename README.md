# Lumen Passport

Lumen porting of Laravel Passport.
The idea come from https://github.com/dusterio/lumen-passport but try to make it transparent with original laravel passport

## Dependencies

* PHP >= 5.6.3
* Lumen >= 5.3

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
        "nomadnt/lumen-passport": "~4.0"
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
    'auth' => App\Http\Middleware\Authenticate::class,
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
    'defaults' => [
        'guard' => 'api'
    ],

    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\User::class
        ]
    ]
];
```

## Registering Routes

Next, you should call the LumenPassport::routes method within the boot method of your application (one of your service providers). 
This method will register the routes necessary to issue access tokens and revoke access tokens, clients, and personal access tokens:

```php

namespace App\Providers;

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
        Passport::routes();
    }
}
   
```

## User model

Make sure your user model uses Passport's ```HasApiTokens``` trait, eg.:

```php

use Laravel\Passport\HasApiTokens;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    // rest of the model
}
```
