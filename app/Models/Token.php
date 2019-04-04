<?php

namespace app\Models;

use PDO;

class Token extends BaseModel
{
    public static function create(int $userId): ?string
    {
        $token = bin2hex(random_bytes(32));

        $result = self::getPDO()
            ->prepare('insert into tokens (user_id, token) values (?, ?);')
            ->execute([$userId, $token]);

        return $result ? $token : null;
    }

    public static function fetch(int $userId): ?string
    {
        $statement = self::getPDO()
            ->prepare('select token from tokens where user_id = ? limit 1;');

        $statement->execute([$userId]);
        $token = $statement->fetch(PDO::FETCH_ASSOC);

        return $token['token'];
    }

    public static function searchUserId(string $token): ?int
    {
        $statement = self::getPDO()
            ->prepare('select user_id from tokens where token = ? limit 1;');

        $statement->execute([$token]);
        $token = $statement->fetch(PDO::FETCH_ASSOC);

        return isset($token['user_id']) ? (int) $token['user_id'] : null;
    }
}