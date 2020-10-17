<?php

namespace Superb\Middlewares\Route;

use Closure;
use Superb\Https\Request;

class RouteDummyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        var_dump("route middleware activated!");
        return $next($request);
    }
}
