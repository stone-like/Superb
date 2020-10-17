<?php

namespace Superb\ServiceProvider;

use Superb\User;
use Superb\UserInterface;

class ServiceProvider
{
    public $provider = [
        UserInterface::class =>
        User::class
    ];
}
