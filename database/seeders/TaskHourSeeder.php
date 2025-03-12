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
        foreach (Task::all() as $task) {
            // each task can have a max 10 task hour records
            TaskHour::factory()
                ->count(fake()->numberBetween(0, 10))
                ->create([
                    'user_id' => $task->user_id,
                    'task_id' => $task->id,
                ]);
        }
    }
}
