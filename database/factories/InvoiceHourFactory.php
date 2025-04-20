<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceHour;
use App\Models\TaskHour;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceHourFactory extends Factory
{
    protected $model = InvoiceHour::class;

    public function definition(): array
    {
        $timestamp = now();

        return [
            'invoice_id' => Invoice::factory(),
            'task_hour_id' => TaskHour::factory(),

            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}
