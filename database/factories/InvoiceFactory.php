<?php

namespace Database\Factories;

use App\Enums\InvoiceStatusEnum;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use App\Services\GeneratorService;
use DateTime;
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
                /** @var DateTime|null $issueDate */
                $issueDate = $attributes['issue_date'];
                $year = $issueDate?->format('Y') ?? $this->faker->year;
                $uniqueNumber = sprintf('%04d', $this->faker->unique()->numberBetween(1, 9999));
                $numberSuffix = "-{$year}-{$uniqueNumber}";

                return GeneratorService::getInitials(Contract::with('customer')->find($attributes['contract_id'])->customer->name) . $numberSuffix;
            },
            'issue_date' => $this->faker->dateTimeBetween('-5 years'),
            'due_date' => fn(array $attributes): DateTime => (clone $attributes['issue_date'])->modify('+7 days'),
            'status' => InvoiceStatusEnum::Draft,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function draft(): Factory
    {
        return $this->state(fn() => [
            'status' => InvoiceStatusEnum::Draft,
            'issue_date' => null,
            'due_date' => null,
        ]);
    }

    public function issued(): Factory
    {
        return $this->state(fn() => ['status' => InvoiceStatusEnum::Issued]);
    }
}
