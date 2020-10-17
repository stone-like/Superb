<?php

namespace Superb\Routing;

use Superb\Container;
use Superb\Routing\Router;

class Route
{
    public $uri;
    public $methods;
    public $action;
    public $router;
    public $container;

    public function __construct($methods, $uri, $action)
    {
        $this->uri = $uri;
        $this->methods = (array) $methods;
        $this->action = $this->parseAction($action);
    }

    public function run()
    {
        $this->container->call([$this->action["Controller"], $this->action["ControllerMethod"]]);
    }

    public function parseAction($action)
    {
        //[UserController::class,"index"]みたいのをController=>UserController、controllerMethod=>"index"みたいにする

        return [
            "Controller" => $action[0],
            "ControllerMethod" => $action[1]
        ];
    }

    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function methods()
    {
        return $this->methods;
    }

    public function uri()
    {
        return $this->uri;
    }
}
