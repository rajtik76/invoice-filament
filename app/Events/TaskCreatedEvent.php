<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;

class TaskCreatedEvent
{
    use Dispatchable;

    public function __construct(public readonly Task $task)
    {
    }
}
