<?php

namespace Superb\Routing;

class RouteGroup
{

    public static function merge($new, $old)
    {
        $new = array_merge(
            $old,
            $new
        );

        return $new;
    }
}
