<?php

namespace Superb\Routing;

use Superb\Container;
use Superb\Https\Pipeline\Pipeline;
use Superb\Https\Request;
use Superb\Middlewares\RouteMiddleware;

class Router
{
    public $container;
    public $routes;
    public $current;
    public $currentRequest;
    private $middlewares = [];
    private $groupStack = [];

    public function __construct(Container $container, RouteCollection $routes)
    {
        $this->container = $container;
        $this->routes = $routes;
    }

    public function setMiddleware()
    {
        $this->middlewares = RouteMiddleware::getMiddleware();
    }

    public function booting()
    {
        //本当はもっと処理がいろいろあるけどいきなりファイルを読み込んでしまう

        $path = $this->getRouteFilePath();
        (new RouteFileRegister($this))->register($path);
    }

    public function getRouteFilePath()
    {
        return __DIR__ . "/api.php";
    }

    public function group(array $attributes, $routes)
    {
        $this->updateGroupStack($attributes);
        $this->loadRoute($routes);
        array_pop($this->groupStack);
    }

    private function updateGroupStack(array $attributes)
    {
        // if ($this->hasGroupStack()) {
        //     //これは例えばgroup自体がnestした時の対策、groupがnestしていないならarray_popされるので、ここは必要ない
        //     $attributes = $this->mergeWithLastGroup();
        // }

        $this->groupStack[] = $attributes;
    }



    private function hasGroupStack()
    {
        return !empty($this->groupStack);
    }

    private function loadRoute($routes)
    {
        $routes($this);
    }

    public function get($uri, $action = null)
    {
        return $this->addRoute("GET", $uri, $action);
    }

    private function addRoute($methods, $uri, $action)
    {
        return $this->routes->add($this->createRoute($methods, $uri, $action));
    }

    private function createRoute($methods, $uri, $action)
    {
        //ここでactionの形は[UserController::class,"index"]
        $route = $this->newRoute($methods, $this->prefix($uri), $action);

        if ($this->hasGroupStack()) {
            //ここで親Groupのmiddlewareとかの情報をこのrouteに混ぜてあげる
            $this->mergeGroupAttributesIntoRoute($route);
        }

        return $route;
    }

    private function mergeGroupAttributesIntoRoute($route)
    {
        $route->setAction($this->mergeWithLastGroup(
            $route->getAction()
        ));
    }

    private function mergeWithLastGroup($new)
    {
        return RouteGroup::merge($new, end($this->groupStack));
    }

    private function newRoute($methods, $uri, $action)
    {

        return (new Route($methods, $uri, $action))->setRouter($this)->setContainer($this->container);
    }

    private function prefix($uri)
    {
        //本当はここでgroupのprefixを追加したりするけど今回はprefix機能作らないので、uriをtrimするだけ
        return trim($uri, "/");
    }

    public function dispatch(Request $request)
    {
        $this->currentRequest = $request;
        return $this->dispatchToRoute($request);
    }

    private function dispatchToRoute(Request $request)
    {
        return $this->runRoute($request, $this->findRoute($request));
    }

    private function findRoute($request)
    {
        $this->current = $route = $this->routes->match($request);
        return $route;
    }

    private function runRoute(Request $request, Route $route)
    {

        $this->middlewares = $this->gatherRouteMiddleware($route);
        return (new Pipeline($this->container))->setPassable($request)->setPipes($this->middlewares)->fire(
            function ($request) use ($route) {
                return $this->prepareResponse($request, $route->run());
            }
        ); //ここでrequestを返す
    }

    public function prepareResponse(Request $request, $response)
    {
        //ここでresponseをいろいろと成形したりする

    }

    private function gatherRouteMiddleware(Route $route)
    {
        //ここでそのrouteのmiddlewareをset
        $action = $route->getAction();
        $this->middlewares = $action["middleware"];
        //ここでgetできるmiddlewareはauth:userみたいな形なんだけど、しっかりPipelineで実際のClassに解決してくれるので平気
        return $this->middlewares;
    }
}
