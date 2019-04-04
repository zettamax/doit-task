<?php

require __DIR__ . '/app/autoload.php';


use app\Models\BaseModel;
use app\Router;

BaseModel::checkConnection();

$router = new Router();
$router->dispatch();

