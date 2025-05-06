<?php
declare(strict_types=1);

namespace App\Actions;

use App\DTO\TaskDTO;
use App\Events\TaskCreatedEvent;
use App\Models\Task;
use App\Traits\UpdateTaskTrait;

final class CreateTaskAction
{
    use UpdateTaskTrait;
    /**
     * Handles the creation of a new Task based on the provided TaskDTO.
     */
    public function handle(TaskDTO $taskDTO): Task
    {
        $task = $this->updateTask(new Task(), $taskDTO);

        TaskCreatedEvent::dispatch($task);

        return $task;
    }
}
