<?php

namespace app\Controllers;

use app\Models\Task;
use app\Models\Token;
use app\Response;

class TaskController
{
    protected $userId;

    public function before()
    {
        $token = $_SERVER['HTTP_AUTH'] ?? null;
        if ($token) {
            $this->userId = Token::searchUserId($token);
        }

        return (bool) $this->userId;
    }

    public function list()
    {
        $input = get_fields($_GET, ['count', 'page', 'sort', 'order']);
        $tasks = Task::getAll($this->userId, $input);

        return Response::apiSuccess($tasks);
    }

    public function create()
    {
        $input = get_fields($_POST, ['title', 'due_date', 'priority']);
        $task = Task::create($input, $this->userId);

        if (!$task) {
            return Response::apiError("Can't create task, try again");
        }

        return Response::apiSuccess($task);
    }

    public function markDone($id)
    {
        $task = Task::markDone($id, $this->userId);

        if (!$task) {
            return Response::apiError("Can't update task");
        }

        return Response::apiSuccess($task);
    }

    public function delete($id)
    {
        $task = Task::delete($id, $this->userId);

        if (!$task) {
            return Response::apiError("Can't delete task");
        }

        return Response::apiSuccess($task);
    }
}