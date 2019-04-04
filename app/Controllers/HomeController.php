<?php

namespace app\Controllers;

use app\Response;

class HomeController
{
    public function index()
    {
        return Response::view('home.html');
    }
}