<?php

namespace app\Controllers;

use app\Models\Token;
use app\Response;
use app\Models\User;

class UserController
{
    public function create()
    {
        $data = get_fields($_POST, ['email', 'password']);

        $userId = User::create($data);
        if (!$userId) {
            return Response::apiError("Can't create user");
        }

        $token = Token::create($userId);

        if (!$token) {
            return Response::apiError("Can't create user token");
        }

        return Response::apiSuccess(['token' => $token]);
    }

    public function login()
    {
        $data = get_fields($_POST, ['email', 'password']);

        $userId = User::login($data);
        if (!$userId) {
            return Response::apiError("Can't login user");
        }

        $token = Token::fetch($userId);
        if (!$token) {
            return Response::apiError("Can't give access to this token");
        }

        return Response::apiSuccess(['token' => $token]);
    }
}