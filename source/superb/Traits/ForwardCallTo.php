<?php

namespace Superb\Traits;

trait ForwardCallTo
{
    public function forwardCallTo($object, $method, $parameters)
    {
        return $object->{$method}(...$parameters);
    }
}
