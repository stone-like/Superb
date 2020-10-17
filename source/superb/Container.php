<?php

namespace Superb;

use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use Superb\Middlewares\Route\AuthenticateMiddleware;
use Superb\Routing\Router;
use Superb\ServiceProvider\ServiceProvider;

class Container
{
    // private $buildStack = [];

    //buildは再帰前提でnestedしていく
    //example class A(B $b),classB(constructorなし)の二つがあるとする
    //classAのフルパス→buildに入り、BをresolveDepsで解決する
    private static $instance;
    private $singletons = [];
    private $bindings = [];



    private $provider;
    public function __construct()
    {
        $this->initializeContainer();
        $this->onBootInitialize();

        // $this->provider = $serviceProvider->provider;

    }

    public static function getInstance()
    {
        return static::$instance;
    }

    private function initializeContainer()
    {
        static::$instance = $this;
        $this->singletons = array_merge($this->singletons, array(Container::class => $this));
    }

    private function onBootInitialize()
    {
        $this->registerProviders();
        $this->bindMiddleware();
        $this->registerRouter();
    }

    private function bindMiddleware()
    {
        $this->bind("auth", AuthenticateMiddleware::class);
    }

    public function bind($bindString, $concrete)
    {
        $this->bindings = array_merge($this->bindings, [
            $bindString => $concrete
        ]);
    }

    private function registerProviders()
    {
        $provider = new ServiceProvider();
        $this->provider = $provider->provider;
    }

    private function registerRouter()
    {
        // $router = $this->make(Router::class);
        // $this->singletons = array_merge($this->singletons, array(Router::class => $router));
        $this->singleton(Router::class);
    }


    public function build(string $concrete)
    {
        //すでにsingletonsにObjectがあるならそれを返す
        if (isset($this->singletons[$concrete])) {
            return $this->singletons[$concrete];
        }


        $reflector = new ReflectionClass($concrete);

        $constructor = $reflector->getConstructor();
        //constructorがない場合はここでnestが終わり
        if (is_null($constructor)) {
            // array_pop($this->buildStack);

            return new $concrete;
        }

        $deps = $constructor->getParameters();

        $instances = $this->resolveDeps($deps);

        //parameterの依存が終わったらもうこのclassの依存関係は解決し終わっているのでpop
        // array_pop($this->buildStack);

        return $reflector->newInstanceArgs($instances);
    }

    public function singleton(string $abstract)
    {
        //argumentなしのしかできないので改良する
        $concrete = $this->getConcrete($abstract);
        $reflector = new ReflectionClass($concrete);

        if ($reflector->isInstantiable()) {
            //public constructorなら引数対策のためmakeで
            $instance = $this->make($concrete);

            return $this->singletons = array_merge($this->singletons, array($concrete => $instance));
        }

        //private constructorなら
        $construct = $reflector->getConstructor(); //一度だけ実行させる
        $construct->setAccessible(true);

        $instance = $reflector->newInstanceWithoutConstructor();

        $construct->invokeArgs($instance, func_get_args());

        return $this->singletons = array_merge($this->singletons, array($concrete => $instance));
    }
    //callではclassのフルパス、method名を指定する、今回は@は考えない(実装するとしても少し処理を追加するだけではあるけど)
    public function call($callback, array $parameters = [])
    {
        //parametersは依存解決が必要なものと必要ないものが混ざる(User　$user、string　$name)とか
        return call_user_func_array($callback, $this->resolveMethodDeps($callback, $parameters));
    }

    private function resolveMethodDeps($callback, array $params)
    {
        $deps = [];

        foreach ($this->getCallReflector($callback)->getParameters() as $parameter) {

            $this->addDependecyForCallParam($parameter, $params, $deps);
        }
        //string $nameみたなただのparameterとUser　$userみたいな依存解決済みの奴を混ぜる
        return array_merge($deps, $params);
    }

    private function getCallReflector($callback)
    {
        //$callbackがarrayならclassMethodで、arrayの第一引数にクラスのフルパス、第二引数にmethod名
        //arrayでないならただのfunction
        return is_array($callback) ? new ReflectionMethod($callback[0], $callback[1]) : new ReflectionFunction($callback);
    }

    //参照渡しでしっかり変更を伝えるところがポイント
    private function addDependecyForCallParam($parameter, &$params, &$deps)
    {
        if (array_key_exists($parameter->name, $params)) {
            //ここでは依存解決の必要がないものをすぐにdepsに移している
            $deps[] = $params[$parameter->name];

            unset($params[$parameter->name]);
        } else if ($parameter->getClass()) {
            //クラスの場合
            $deps[] = $this->make($parameter->getClass()->name); //クラスの依存を解決しに行く
        } else if ($parameter->isDefaultValueAvailable()) {
            $deps[] = $parameter->getDefaultValue();
        }
    }

    private function resolveDeps(array $deps)
    {
        $results = [];

        foreach ($deps as $dep) {

            //fullPathがない、つまりclassで定義されていないならPrimitiveとして解決
            $result = is_null(
                Util::getFullParh($dep)
            ) ? $this->resolvePrimitive($dep) : $this->resolveClass($dep);

            $results[] = $result;
        }

        return $results;
    }

    private function resolvePrimitive(ReflectionParameter $param)
    {
        //defaultValueがコンストラクタであるなら。defaultValueを返す
        //ただ、普通はparamterとしてclassと一緒に処理されるはず
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }
    }

    private function resolveClass(ReflectionParameter $dep)
    {
        return $this->make(Util::getFullParh($dep));
    }

    public function make($abstract, array $params = [])
    {

        return $this->resolve($abstract, $params);
    }

    protected function resolve($abstract, $params = [])
    {
        $concrete  = $this->getConcrete($abstract);

        $object = $this->build($concrete); //フルパスからbuild

        return $object;
    }

    private function getConcrete($abstract)
    {
        //ここでserviceProviderに登録されているならそこのフルパスと交換
        if (isset($this->provider[$abstract])) {
            return $this->provider[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract];
        }
        return $abstract;
    }
}
