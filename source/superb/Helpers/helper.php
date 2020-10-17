<?php

use Superb\Container;
use Superb\Routing\Router;

function DIContainer()
{
    return Container::getInstance();
}

function Router()
{
    return DIContainer()->make(Router::class);
}
