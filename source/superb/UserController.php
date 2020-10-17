<?php

namespace Superb;

use Superb\User;
use Superb\Https\Status;
use Superb\Https\Request;
use Superb\UserInterface;
use Superb\Https\Response;

class UserController
{
    private $user;


    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }
    public function setUserName(string $name)
    {
        return $this->user->setName($name);
    }

    public function getUserName()
    {
        return $this->user->Name();
    }

    public function run(Request $request)
    {

        $content = '';
        $content .= 'Method type:' . $request->methodType() . '<br>';
        $content .= 'Header Connection:' . $request->header('Connection') . '<br>';
        $content .= 'Host :' . $request->host() . '<br>';
        $content .= 'Request uri:' . $request->requestUri() . '<br>';
        $content .= 'Path info:' . $request->pathInfo() . '<br>';
        $content .= 'GET name:' . $request->get('name') . '<br>';
        $content .= 'GET aaa:' . $request->get('aaa') . '<br>';

        $response = new Response($content);

        $response->send();
    }
}
