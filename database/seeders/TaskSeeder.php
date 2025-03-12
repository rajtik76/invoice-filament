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
        foreach (Contract::all() as $contract) {
            // each contract have max 10 tasks
            Task::factory(fake()->numberBetween(1, 10))->create([
                'user_id' => $contract->user_id,
                'contract_id' => $contract->id,
            ]);
            // insert at least 1 inactive task
            Task::factory(fake()->numberBetween(1, 3))->create([
                'user_id' => $contract->user_id,
                'contract_id' => $contract->id,
                'active' => false,
            ]);
        }
    }
}
