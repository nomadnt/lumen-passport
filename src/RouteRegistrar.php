<?php

namespace Nomadnt\LumenPassport;

use Laravel\Lumen\Routing\Router;
use Laravel\Passport\RouteRegistrar as Registrar;

class RouteRegistrar extends Registrar
{
    /**
     * Create a new route registrar instance.
     *
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
