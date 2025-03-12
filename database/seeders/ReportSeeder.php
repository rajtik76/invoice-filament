<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Report;
use App\Models\TaskHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ReportSeeder extends Seeder
{
    protected array $ttt = [];

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
                            fn (Collection $monthCollection, $month) => $this->ttt[] = [
                                'contract_id' => intval($contractId),
                                'user_id' => intval($userId),
                                'year' => intval($year),
                                'month' => intval($month),
                            ]
                        )
                    )
                )
            );

        foreach ($this->ttt as $taskHour) {
            Report::factory()->create([
                'user_id' => $taskHour['user_id'],
                'contract_id' => $taskHour['contract_id'],
                'year' => $taskHour['year'],
                'month' => $taskHour['month'],
            ]);
        }
    }
}
