<?php

namespace Superb\Middlewares;

use Superb\Middlewares\Route\RouteDummyMiddleware;

class RouteMiddleware
{
    private static $middlewares = [RouteDummyMiddleware::class];
    public static function getMiddleware()
    {
        return static::$middlewares;
    }
}
