<?php

namespace Superb\Routing;

class RouteFileRegister
{
    private $router;
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function register($routeFile)
    {
        require $routeFile;
    }
}
