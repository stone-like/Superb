<?php

use Superb\User;
use Superb\Container;
use Superb\Https\Request;
use Superb\UserController;
use Superb\Https\Kernel\Kernel;
use Superb\Routing\Router;
use Superb\ServiceProvider\ServiceProvider;

require("vendor/autoload.php");

$container = new Container();
// $container->singleton(Kernel::class);
$container->singleton(Request::class);
// $container->singleton(Router::class);//



$kernel = $container->make(Kernel::class);

$response = $kernel->handle(
    $request = $container->make(Request::class)
);
//何らかの手段でrouteからcontrollerのfullpathを取得したと仮定
// $userContainer = $container->build(UserController::class);

//コントローラーのmethodを呼ぶとき(routing)では依存解消のためにcontainer->callから呼ぶようにしよ,引数が別にあるときは最後に依存解決したInstanceと引数をarray_mergeで混ぜてあげればよさそう
// $container->call([UserController::class, "run"]);
