<?php

namespace app\Controllers;

use app\Response;

class ErrorController
{
    public function _404()
    {
        http_response_code(404);
        return Response::view('404.html');
    }
}