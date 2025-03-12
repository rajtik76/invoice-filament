<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskHour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TaskHourFactory extends Factory
{
    protected $model = TaskHour::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => User::factory(),
            'task_id' => fn (array $attributes) => Task::factory()->create(['user_id' => $attributes['user_id']]),
            'date' => $this->faker->dateTimeBetween(now()->subMonth()),
            'hours' => $this->faker->randomFloat(1, 0.5, 8),
            'comment' => $this->faker->boolean(10) ? $this->faker->sentence() : null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
