<?php

namespace Superb\Routing;

use Exception;
use Superb\Https\Request;
use Superb\Routing\Route;

class RouteCollection
{
    private $routes = [];
    private $allRoutes = [];
    private $actionList = [];

    public function match(Request $request)
    {
        $method = $request->methodType();
        $uri = $this->trimURI($request);
        return $this->getActualRoute($method, $uri);
    }

    public function trimURI($request)
    {

        // $queryTrimedUri = strtok($request->requestUri(), "?");
        // $uri = trim($queryTrimedUri, "/");
        $uri = $request->pathinfo();
        return $uri;
    }

    private function getActualRoute($method, $uri)
    {
        if (!isset($this->routes[$method])) {
            throw new Exception("invalid method!");
        }
        $methodFilteredRoutes = $this->routes[$method];

        if (!isset($methodFilteredRoutes[$uri])) {
            throw new Exception("invalid route!");
        }

        return $methodFilteredRoutes[$uri];
    }

    public function add(Route $route)
    {
        $this->addToCollections($route);
        $this->addLookups($route);

        return $route;
    }

    private function addToCollections($route)
    {
        $uri = $route->uri();
        $methods = $route->methods();

        foreach ($methods as $method) {
            $this->routes[$method][$uri] = $route;
        }

        $this->allRoutes[$method . $uri] = $route;
    }

    private function addLookups($route)
    {
        $action = $route->getAction();

        if (isset($action["Controller"])) {
            $this->addToActionList($action, $route);
        }
    }

    private function addToActionList($action, $route)
    {
        // ["UserController::class" => $route]みたいな感じ
        $this->actionList[$action["Controller"]] = $route;
    }
}
