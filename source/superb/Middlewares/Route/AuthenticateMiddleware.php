<?php

namespace Superb\Middlewares\Route;

use Closure;
use Superb\Https\Request;

class AuthenticateMiddleware
{
    //authNameは対象にしたいTable名
    public function handle(Request $request, Closure $next, string $authName)
    {
        // var_dump("authenticate middleware activated!");
        //例えばuserLogin(おそらくsessionを使う)、これでだめだったら$nextに行かずにfalseなりthrowを投げれば$nextには行かない
        //nextに行かないということはdispatchToRouterがそもそも発火しないのでコントローラーまでも行くことはない
        return $next($request);
    }
}
