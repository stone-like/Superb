<?php

namespace Superb\Https\Pipeline;

use Closure;
use Exception;
use Throwable;
use Superb\Container;

class Pipeline
{

    private $container;
    private $passableObj; //requestなど
    private $pipes = [];
    private $method = "handle"; //ここで各pipelineでhandleMethodを発火させることを強制

    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }

    public function setPassable($passableObj)
    {
        $this->passableObj = $passableObj;
        return $this;
    }

    public function setPipes(array $pipes)
    {
        $this->pipes = $pipes;

        return $this;
    }

    public function fire(Closure $destination)
    {
        //array_reduceは第一引数にiterationするarray、第二引数に適用する関数(その関数の引数にarrayのpreviousとcurrentが入る)、
        //第三引数に初期値
        $pipeline = array_reduce(
            array_reverse($this->pipes()),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($this->passableObj);
    }

    public function pipes()
    {
        return $this->pipes;
    }

    private function carry()
    {
        //carryはarray_reduceの第二引数なのでpreviousとcurrentがarrayから渡される
        //previousもcurrentもそれぞれmiddleware
        return function ($stack, $pipe) {
            return function ($passableObj) use ($stack, $pipe) {
                try {
                    if (is_callable($pipe)) {
                        return $pipe($passableObj, $stack);
                    } elseif (!is_object($pipe)) {
                        [$name, $parameters] = $this->parsePipeString($pipe);
                        $pipe = $this->getContainer()->make($name);
                        $parameters = array_merge([$passableObj, $stack], $parameters);
                    } else {
                        $parameters = [$passableObj, $stack];
                    }


                    $carry = method_exists($pipe, $this->method)
                        ? $pipe->{$this->method}(...$parameters)
                        : $pipe(...$parameters);

                    return $this->handleCarry($carry); //ここでの$carryはhandleでの$nextなので次回のpipeでこれが発火されることになる
                    //なのでpipeで使うmiddlewareは必然的にhndleだったり$nextを実装しなければいけない
                } catch (Throwable $e) {
                    throw new Exception($e->getMessage());
                }
            };
        };
    }

    private function handleCarry($carry)
    {
        return $carry;
    }


    public function getContainer()
    {
        return $this->container;
    }

    private function parsePipeString(string $pipe)
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        //例として"auth:user"みたいのを[auth,user]みたいにする

        return [$name, $parameters];
    }



    private function prepareDestination(Closure $destination)
    {
        return function ($passableObj) use ($destination) {
            try {
                return $destination($passableObj);
            } catch (Throwable $e) {
                throw new Exception($e->getMessage());
            }
        };
    }
}
