<?php

namespace Superb;

use Superb\UserInterface;

class User implements UserInterface
{
    private $name;
    public function __construct()
    {
    }
    public function SetName(string $name)
    {
        $this->name = $name;
    }
    public function Name()
    {
        return $this->name;
    }
}
