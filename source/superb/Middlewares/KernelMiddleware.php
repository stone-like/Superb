<?php

namespace Superb\Middlewares;

use Superb\Middlewares\Kernel\KerDummyMiddleware;

class KernelMiddleware
{
    private static $middlewares = [KerDummyMiddleware::class];
    public static function getMiddleware()
    {
        return static::$middlewares;
    }
}
