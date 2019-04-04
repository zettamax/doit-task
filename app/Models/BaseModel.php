<?php

namespace app\Models;

use PDO;

class BaseModel
{
    protected static $dbFilename;
    protected static $pdo;

    protected static $init = [

'create table users (
  id integer primary key,
  email varchar(100) not null unique,
  password varchar(100) not null
);
',
'create table tokens (
  id integer primary key,
  user_id integer not null,
  token varchar(200) not null,
  foreign key (user_id) references users(id)
);
',
'
create table tasks (
  id integer primary key,
  title varchar(200) not null,
  due_date integer not null,
  priority tinyint not null,
  done tinyint not null default 0,
  user_id integer not null,
  foreign key (user_id) references users(id)
);
'
    ];

    public static function initDB()
    {
        self::$dbFilename = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'db.sqlite';
        $exists = file_exists(self::$dbFilename);
        $pdo = self::getPDO();
        if (!$exists) {
            foreach (self::$init as $sql) {
                $pdo->exec($sql);
            }
        }
    }

    public static function getPDO(): PDO
    {
        if (!self::$pdo) {
            self::$pdo = new PDO('sqlite:' . self::$dbFilename);
        }

        return self::$pdo;
    }
}