<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => User::factory(),
            'contract_id' => fn () => Contract::factory(),
            'year' => $this->faker->numberBetween(2000, now()->subYear()->year),
            'month' => $this->faker->month,
            'content' => [],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
