<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;

class TaskUpdatedEvent
{
    use Dispatchable;

    public function __construct(public Task $task)
    {
    }
}
