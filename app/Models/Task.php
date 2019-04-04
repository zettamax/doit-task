<?php

namespace app\Models;

use PDO;

class Task extends BaseModel
{
    public static function create($data, $userId)
    {
        $valid = self::validateInputCreate($data);
        if (!$valid) {
            return false;
        }

        $pdo = self::getPDO();
        $result = $pdo
            ->prepare('insert into tasks (title, due_date, priority, user_id) values (?, ?, ?, ?)')
            ->execute([$valid['title'], $valid['due_date'], $valid['priority'], $userId]);

        if (!$result) {
            return false;
        }

        $taskId = (int) $pdo->lastInsertId();
        $task = self::getById($taskId, $userId);

        return self::transformDate($task);
    }

    public static function getById(int $taskId, int $userId): ?array
    {
        $statement = self::getPDO()
            ->prepare(<<<QUERY
select id, title, due_date, priority, done 
from tasks 
where id = ? and user_id = ?;
QUERY
);
        $statement->execute([$taskId, $userId]);
        $task = $statement->fetch(PDO::FETCH_ASSOC);
        return $task ? $task : null;
    }

    public static function getAll(int $userId, array $options): array
    {
        $valid = self::validateInputGet($options);
        $offset = ($valid['page'] - 1) * $valid['count'];

        $statement = self::getPDO()
            ->prepare(<<<QUERY
select id, title, due_date, priority, done 
from tasks 
where user_id = ?
order by {$valid['sort']} {$valid['order']}
limit ?, ?;
QUERY
            );
        $statement->execute([$userId, $offset, $valid['count']]);
        $tasks = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($task) {
            return self::transformDate($task);
        }, $tasks);
    }

    public static function markDone($id, $userId): ?array
    {
        $statement = self::getPDO()
            ->prepare('update tasks set done = 1 where id = ? and user_id = ?;');
        $success = $statement->execute([$id, $userId]);
        $rows = $statement->rowCount();
        if (!$success || !$rows) {
            return null;
        }

        $statement->fetch(PDO::FETCH_ASSOC);
        $task = self::getById($id, $userId);

        return self::transformDate($task);
    }

    public static function delete($id, $userId): ?array
    {
        $task = self::getById($id, $userId);
        if (!$task) {
            return null;
        }

        $statement = self::getPDO()
            ->prepare('delete from tasks where id = ? and user_id = ?;');
        $success = $statement->execute([$id, $userId]);
        $rows = $statement->rowCount();
        if (!$success || !$rows) {
            return null;
        }

        return self::transformDate($task);
    }

    private static function validateInputCreate($data): ?array
    {
        $checked = [];

        foreach ($data as $key => $item) {
            switch ($key) {
                case 'title':
                    if (strlen($item) < 3) {
                        return null;
                    }
                    $checked[$key] = $item;
                    break;

                case 'due_date':
                    $timestamp = strtotime("+1 week");
                    if (!empty($item)) {
                        $timestamp = strtotime($item);
                        if ($timestamp === false || $timestamp < time()) {
                            return null;
                        }
                    }
                    $checked[$key] = $timestamp;
                    break;

                case 'priority':
                    if (empty($item)) {
                        $item = 2;
                    }
                    $priority = filter_var($item, FILTER_VALIDATE_INT);
                    if ($priority === false || $priority < 1 || $priority > 3) {
                        return null;
                    }
                    $checked[$key] = $priority;
                    break;
            }
        }

        return $checked;
    }

    private static function validateInputGet(array $options): array
    {
        $checked = [];

        foreach ($options as $key => $item) {
            switch ($key) {
                case 'count':
                    $count = filter_var($item, FILTER_VALIDATE_INT);
                    if (!$count || $count < 1 || $count > 100) {
                        $count = 10;
                    }
                    $checked[$key] = $count;
                    break;

                case 'page':
                    $page = filter_var($item, FILTER_VALIDATE_INT);
                    if (!$page || $page < 1) {
                        $page = 1;
                    }
                    $checked[$key] = $page;
                    break;

                case 'sort':
                    if (is_null($item)) {
                        $item = 'due_date';
                    }
                    if (!in_array($item, ['title', 'due_date', 'priority'])) {
                        $item = 'due_date';
                    }
                    $checked[$key] = $item;
                    break;

                case 'order':
                    if (is_null($item)) {
                        $item = 'asc';
                    }
                    if (!in_array($item, ['asc', 'desc'])) {
                        $item = 'asc';
                    }
                    $checked[$key] = $item;
                    break;
            }
        }

        return $checked;
    }

    private static function transformDate($task)
    {
        $task['due_date'] = date('Y-m-d H:i:s', $task['due_date']);
        return $task;
    }
}