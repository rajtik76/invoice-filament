<?php
declare(strict_types=1);

namespace App\Traits;

use App\DTO\TaskDTO;
use App\Models\Task;

trait UpdateTaskTrait
{
    public function updateTask(Task $task, TaskDTO $taskDTO): Task {
        $task->contract_id = $taskDTO->contract_id;
        $task->user_id = $taskDTO->user_id;
        $task->name = $taskDTO->name;
        $task->url = $taskDTO->url;
        $task->note = $taskDTO->note;
        $task->active = $taskDTO->active;

        $task->save();

        return $task;
    }
}
