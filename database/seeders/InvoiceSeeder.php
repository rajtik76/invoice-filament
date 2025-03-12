<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\TaskHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        TaskHour::with('task.contract')->get()
            // I need only task + year + month
            ->map(fn (TaskHour $taskHour) => [
                'contract' => $taskHour->task->contract,
                'year' => sprintf('%04d', $taskHour->date->year),
                'month' => sprintf('%02d', $taskHour->date->month),
                'date' => $taskHour->date,
            ])
            // group by task + year + month
            ->groupBy(fn ($item) => "{$item['contract']->id}{$item['year']}{$item['month']}")
            ->each(function (Collection $item) {
                // take first item
                $firstItem = $item->first();

                // and create invoice model
                Invoice::factory()->create([
                    'user_id' => $firstItem['contract']->user_id,
                    'contract_id' => $firstItem['contract']->id,
                    'issue_date' => $firstItem['date'],
                ]);
            });
    }
}
