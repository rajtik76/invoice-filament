<?php

namespace Database\Factories;

use App\Enums\CurrencyEnum;
use App\Enums\InvoiceStatusEnum;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use App\Services\GeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'contract_id' => Contract::factory(),
            'number' => function (array $attributes) {
                $year = $attributes['issue_date']?->year ?? $this->faker->year;
                $month = sprintf('%03d', $attributes['issue_date']?->month ?? $this->faker->month);
                $numberSuffix = "-{$year}-{$month}";

                return GeneratorService::getInitials(Contract::with('customer')->find($attributes['contract_id'])->customer->name) . $numberSuffix;
            },
            'issue_date' => null,
            'due_date' => null,
            'status' => InvoiceStatusEnum::Draft,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function draft(): Factory
    {
        return $this->state(function () {
            return [
                'status' => InvoiceStatusEnum::Draft,
                'issue_date' => null,
                'due_date' => null,
            ];
        });
    }

    public function issued(): Factory
    {
        return $this->state(function () {
            return [
                'status' => InvoiceStatusEnum::Issued,
                'issue_date' => fn() => $this->faker->dateTimeBetween('-5 years'),
                'due_date' => fn(array $attributes) => (clone $attributes['issue_date'])->modify('+14 days'),
            ];
        });
    }
}
