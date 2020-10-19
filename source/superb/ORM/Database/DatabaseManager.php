<?php

namespace Superb\ORM\Database;

use Superb\Container;
use Superb\ORM\Database\Connection\ConnectionFactory;

class DatabaseManager
{
    private $container;
    private $factory;

    public function __construct(Container $container, ConnectionFactory $factory)
    {
        $this->container = $container;
        $this->factory = $factory;
    }
}
