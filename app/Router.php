<?php

namespace app;

class Router
{
    protected $route;
    protected $method;
    protected $routeData;

    protected static $defaultCtrlNS = 'app\\Controllers\\';

    protected static $routes = [
        '~^/$~' => [
            'GET' => ['HomeController', 'index'],
        ],
        '~^/users$~' => [
            'POST' => ['UserController', 'create'],
        ],
        '~^/users/sign-in$~' => [
            'POST' => ['UserController', 'login'],
        ],
        '~^/tasks$~' => [
            'GET' => ['TaskController', 'list'],
            'POST' => ['TaskController', 'create'],
        ],
        '~^/tasks/(?<id>\d+)$~' => [
            'DELETE' => ['TaskController', 'delete'],
        ],
        '~^/tasks/(?<id>\d+)/done$~' => [
            'POST' => ['TaskController', 'markDone'],
        ],
    ];

    public function __construct()
    {
        $currentRoute = null;
        $routeData = null;
        $uri = strtok($_SERVER["REQUEST_URI"],'?');

        foreach (self::$routes as $route => $verbs) {
            $routeMatched = preg_match($route, $uri, $data);
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            $verbMatched = array_key_exists($method, $verbs);
            if ($routeMatched && $verbMatched) {
                $this->route = $verbs[$method];
                $this->method = $method;
                $this->routeData = $data;
                break;
            }
        }

        if (!$this->route) {
            $this->route = ['ErrorController', '_404'];
        }
    }

    public function dispatch()
    {
        $ctrlClass = self::$defaultCtrlNS . $this->route[0];
        if (!class_exists($ctrlClass, true)) {
            die("Can't found required class");
        }
        
        $controller = new $ctrlClass;
        $method = $this->route[1];
        $params = isset($this->routeData['id']) ? [(int) $this->routeData['id']] : [];
        
        if (!method_exists($controller, $method)) {
            die("Can't found controller method");
        }

        $authResult = true;
        if (method_exists($controller, 'before')) {
            $authResult = call_user_func([$controller, 'before']);
        }

        if ($authResult) {
            $output = call_user_func_array([$controller, $method], $params);
        } else {
            http_response_code(403);
            $output = Response::apiError('Wrong auth token');
        }

        echo $output;
    }

}