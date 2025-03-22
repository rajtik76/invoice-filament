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
        TaskHour::with(['user', 'task.contract'])->get()
            // We need only task + year + month
            ->map(fn (TaskHour $taskHour) => [
                'contract' => $taskHour->task->contract,
                'user' => $taskHour->user,
                'year' => sprintf('%04d', $taskHour->date->year),
                'month' => sprintf('%02d', $taskHour->date->month),
                'date' => $taskHour->date,
            ])
            // Group by task + year + month
            ->groupBy(fn ($item) => "{$item['contract']->id}{$item['year']}{$item['month']}")
            ->each(function (Collection $item) {
                // Take first item
                $firstItem = $item->first();

                // and create invoice model
                Invoice::factory()
                    ->recycle([$firstItem['user'], $firstItem['contract']])
                    ->create(['issue_date' => $firstItem['date']]);
            });
    }
}
