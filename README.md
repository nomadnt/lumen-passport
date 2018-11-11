# Lumen Passport

Lumen porting of Laravel Passport

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

### Installed routes

This package mounts the following routes after you call routes() method (see instructions below):

Verb | Path | NamedRoute | Controller | Action | Middleware
--- | --- | --- | --- | --- | ---
POST   | /oauth/token                             |            | \Laravel\Passport\Http\Controllers\AccessTokenController           | issueToken | -
GET    | /oauth/tokens                            |            | \Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController | forUser    | auth
DELETE | /oauth/tokens/{token_id}                 |            | \Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController | destroy    | auth
POST   | /oauth/token/refresh                     |            | \Laravel\Passport\Http\Controllers\TransientTokenController        | refresh    | auth
GET    | /oauth/clients                           |            | \Laravel\Passport\Http\Controllers\ClientController                | forUser    | auth
POST   | /oauth/clients                           |            | \Laravel\Passport\Http\Controllers\ClientController                | store      | auth
PUT    | /oauth/clients/{client_id}               |            | \Laravel\Passport\Http\Controllers\ClientController                | update     | auth
DELETE | /oauth/clients/{client_id}               |            | \Laravel\Passport\Http\Controllers\ClientController                | destroy    | auth
GET    | /oauth/scopes                            |            | \Laravel\Passport\Http\Controllers\ScopeController                 | all        | auth
GET    | /oauth/personal-access-tokens            |            | \Laravel\Passport\Http\Controllers\PersonalAccessTokenController   | forUser    | auth
POST   | /oauth/personal-access-tokens            |            | \Laravel\Passport\Http\Controllers\PersonalAccessTokenController   | store      | auth
DELETE | /oauth/personal-access-tokens/{token_id} |            | \Laravel\Passport\Http\Controllers\PersonalAccessTokenController   | destroy    | auth

Please note that some of the Laravel Passport's routes had to 'go away' because they are web-related and rely on sessions (eg. authorise pages). Lumen is an
API framework so only API-related routes are present.

## Configuration

Edit config/auth.php to suit your needs. A simple example:

```php
return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
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

Load the config in `bootstrap/app.php` since Lumen doesn't load config files automatically:

```php
$app->configure('auth');
```

## Registering Routes

Next, you should call the LumenPassport::routes method within the boot method of your application (one of your service providers). 
This method will register the routes necessary to issue access tokens and revoke access tokens, clients, and personal access tokens:

```php
\Nomadnt\LumenPassport\LumenPassport::routes();
```

## User model

Make sure your user model uses Passport's ```HasApiTokens``` trait, eg.:

```php
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    /* rest of the model */
}
```
