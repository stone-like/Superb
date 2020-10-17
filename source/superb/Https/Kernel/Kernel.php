<?php

namespace Superb\Https\Kernel;

use Superb\Container;
use Superb\Https\Pipeline\Pipeline;
use Superb\Middlewares\KernelMiddleware;
use Superb\Routing\Router;

class Kernel
{
    private $container;
    private $router;
    private $middlewares = [];

    public function __construct(Container $container, Router $router)
    {
        $this->container = $container;
        $this->router = $router;
        $this->middlewares = KernelMiddleware::getMiddleware();
    }

    public function handle($request)
    {
        $response = $this->sendRequestThroughRouter($request);
    }

    private function sendRequestThroughRouter($request)
    {
        $this->bootstrap();

        return (new Pipeline($this->container))->setPassable($request)->setPipes($this->middlewares)->fire($this->dispatchToRouter());
    }

    private function bootstrap()
    {
        //routerにmiddlewareをset
        $this->router->setMiddleware();
        //routerにapi.phpからrouteを読み込ませる
        $this->router->booting();
    }

    private function dispatchToRouter()
    {
        return function ($request) {
            return $this->router->dispatch($request);
        };
    }
}
