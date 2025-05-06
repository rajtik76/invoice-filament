<?php
declare(strict_types=1);

namespace App\Actions;

use App\DTO\TaskDTO;
use App\Events\TaskUpdatedEvent;
use App\Models\Task;
use App\Traits\UpdateTaskTrait;

final class EditTaskAction
{
    use UpdateTaskTrait;

    public function handle(Task $task, TaskDTO $taskDTO): Task
    {
        $task = $this->updateTask($task, $taskDTO);

        TaskUpdatedEvent::dispatch($task);

        return $task;
    }
}
