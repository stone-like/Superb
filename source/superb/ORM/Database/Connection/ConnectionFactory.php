<?php

namespace Superb\ORM\Database\Connection;

use Superb\Container;

class ConnectionFactory
{
    private $container;
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
