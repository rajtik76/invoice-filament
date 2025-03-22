<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskHour;
use Illuminate\Database\Seeder;

class TaskHourSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Task::with(['user'])->get() as $task) {
            // Each task have between 2-5 records
            TaskHour::factory()
                ->count(fake()->numberBetween(2, 5))
                ->recycle($task->user, $task)
                ->create();
        }
    }
}
