<?php

namespace Superb\Https\Controller;

use Superb\Https\Request;
use Superb\Https\Response;

class TestController
{
    public function index(Request $request)
    {
        $content = '';
        $content .= 'Method type:' . $request->methodType() . '<br>';
        $content .= 'Header Connection:' . $request->header('Connection') . '<br>';
        $content .= 'Host :' . $request->host() . '<br>';
        $content .= 'Request uri:' . $request->requestUri() . '<br>';
        $content .= 'Path info:' . $request->pathInfo() . '<br>';
        $content .= 'GET name:' . $request->get('name') . '<br>';
        $content .= 'GET aaa:' . $request->get('aaa') . '<br>';

        return  new Response($content);
    }
}
