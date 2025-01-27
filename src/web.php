<?php

$router->post('/token', [
    'uses' => 'AccessTokenController@issueToken',
    'as' => 'token',
    'middleware' => 'throttle',
]);

$router->get('/authorize', [
    'uses' => 'AuthorizationController@authorize',
    'as' => 'authorizations.authorize',
    'middleware' => 'web',
]);

$router->group(['middleware' => ['web', 'auth:api']], function () use ($router) {
    $router->post('/token/refresh', [
        'uses' => 'TransientTokenController@refresh',
        'as' => 'token.refresh',
    ]);

    $router->post('/authorize', [
        'uses' => 'ApproveAuthorizationController@approve',
        'as' => 'authorizations.approve',
    ]);

    $router->delete('/authorize', [
        'uses' => 'DenyAuthorizationController@deny',
        'as' => 'authorizations.deny',
    ]);

    $router->get('/tokens', [
        'uses' => 'AuthorizedAccessTokenController@forUser',
        'as' => 'tokens.index',
    ]);

    $router->delete('/tokens/{token_id}', [
        'uses' => 'AuthorizedAccessTokenController@destroy',
        'as' => 'tokens.destroy',
    ]);

    $router->get('/clients', [
        'uses' => 'ClientController@forUser',
        'as' => 'clients.index',
    ]);

    $router->post('/clients', [
        'uses' => 'ClientController@store',
        'as' => 'clients.store',
    ]);

    $router->put('/clients/{client_id}', [
        'uses' => 'ClientController@update',
        'as' => 'clients.update',
    ]);

    $router->delete('/clients/{client_id}', [
        'uses' => 'ClientController@destroy',
        'as' => 'clients.destroy',
    ]);

    $router->get('/scopes', [
        'uses' => 'ScopeController@all',
        'as' => 'scopes.index',
    ]);

    $router->get('/personal-access-tokens', [
        'uses' => 'PersonalAccessTokenController@forUser',
        'as' => 'personal.tokens.index',
    ]);

    $router->post('/personal-access-tokens', [
        'uses' => 'PersonalAccessTokenController@store',
        'as' => 'personal.tokens.store',
    ]);

    $router->delete('/personal-access-tokens/{token_id}', [
        'uses' => 'PersonalAccessTokenController@destroy',
        'as' => 'personal.tokens.destroy',
    ]);
});