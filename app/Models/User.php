<?php

namespace app\Models;

use PDO;

class User extends BaseModel
{
    public static function create(array $data): ?int
    {
        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        $password = $data['password'];
        if (!$email) {
            return null;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $result = self::getPDO()
            ->prepare('insert into users (email, password) values (?, ?);')
            ->execute([$email, $hash]);

        if (!$result) {
            return null;
        }

        $userId = (int) self::getPDO()->lastInsertId();

        return $userId;
    }

    public static function fetchByEmail(string $email): ?array
    {
        $statement = self::getPDO()
                ->prepare('select id, password, email from users where email = ?;');
        $statement->execute([$email]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user ? $user : null;
    }

    public static function login(array $data): ?int
    {
        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        $password = $data['password'];

        if (!$email) {
            return null;
        }

        if (!($user = self::fetchByEmail($email))) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        return $user['id'];
    }
}