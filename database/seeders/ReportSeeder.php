<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Report;
use App\Models\TaskHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        TaskHour::join('tasks', 'tasks.id', '=', 'task_hours.task_id')
            ->select(['tasks.contract_id', 'task_hours.user_id', 'task_hours.date'])
            ->get()
            ->map(fn (TaskHour $taskHour) => [
                ...$taskHour->toArray(),
                'year' => $taskHour->date->format('Y'),
                'month' => $taskHour->date->format('m'),
            ])
            ->groupBy(['contract_id', 'user_id', 'year', 'month'])
            ->each(
                fn (Collection $taskHour, $contractId) => $taskHour->each(
                    fn (Collection $contractCollection, $userId) => $contractCollection->each(
                        fn (Collection $yearCollection, $year) => $yearCollection->each(
                            function (Collection $monthCollection, $month) use ($contractId, $userId, $year) {
                                Report::factory()->create([
                                    'user_id' => $userId,
                                    'contract_id' => $contractId,
                                    'year' => $year,
                                    'month' => $month,
                                ]);
                            }
                        )
                    )
                )
            );
    }
}
