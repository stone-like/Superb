<?php

namespace Superb\Middlewares\Kernel;

use Closure;
use Superb\Https\Request;

class KerDummyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // var_dump("Ker middleware activated!");
        return $next($request);
    }
}
