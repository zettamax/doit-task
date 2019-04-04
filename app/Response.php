<?php

namespace app;

class Response
{
    public static function api($data)
    {
        return json_encode($data);
    }

    public static function apiError($message)
    {
        http_response_code(400);
        return self::api([
            'success' => false,
            'message' => $message,
            'data' => [],
        ]);
    }

    public static function apiSuccess($data)
    {
        return self::api([
            'success' => true,
            'message' => '',
            'data' => $data,
        ]);
    }

    public static function view($file)
    {
        ob_start();
        include dirname(__DIR__) . '/app/views/' . $file;
        $output = ob_get_contents();
        ob_get_clean();

        return $output;
    }
}