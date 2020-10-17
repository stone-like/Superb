<?php

namespace Superb;

class Util
{
    //本家の名前ではgetParameterClassNameとなっている
    public static function getFullParh($param)
    {
        $type = $param->getType();

        $name = $type->getName();
        //ここにselfだったりparentだったりの処理も入る
        return $name;
    }
}
