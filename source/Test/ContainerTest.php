<?php

use Superb\Container;
use PHPUnit\Framework\TestCase;
use Superb\ORM\Database\DatabaseManager;
use Superb\UserController;

class ContainerTest extends TestCase
{

    /** @test */
    public function can_create_container()
    {
        $container = new Container();
        $container->bind("test", "TestClass");
        $this->assertEquals("TestClass", $container->getBindings()["test"]);
    }

    /** @test */
    public function can_create_dbManager()
    {
        $container = new Container();

        $this->assertEquals(DatabaseManager::class, get_class($container["db"]));
    }
}
