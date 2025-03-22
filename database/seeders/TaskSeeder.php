<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Contract::with('user')->get() as $contract) {
            // Each contract have 10 tasks
            Task::factory()
                ->count(10)
                ->recycle([$contract->user, $contract])
                ->active()
                ->create();

            // Insert 3 inactive task
            Task::factory()
                ->count(3)
                ->recycle([$contract->user, $contract])
                ->inactive()
                ->create();
        }
    }
}
