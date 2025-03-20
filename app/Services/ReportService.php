<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Contract;
use App\Models\TaskHour;

class ReportService
{
    /**
     * Create report content
     *
     * @return array{
     *     name: string,
     *     url: ?string,
     *     date: string,
     *     hours: float,
     *     comment: ?string
     * }
     */
    public static function createContent(Contract $contract, ?int $year = null, ?int $month = null): array
    {
        if (! $year) {
            $year = now()->year;
        }
        if (! $month) {
            $month = now()->month;
        }

        return TaskHour::with('task')
            ->whereIn('task_id', $contract->tasks()->pluck('id'))
            ->whereYear('date', strval($year))
            ->whereMonth('date', strval($month))
            ->orderBy('date')
            ->get()
            ->map(fn (TaskHour $taskHour) => [
                'name' => $taskHour->task->name,
                'url' => $taskHour->task->url,
                'date' => $taskHour->date->toDateString(),
                'hours' => floatval($taskHour->hours),
                'comment' => $taskHour->comment,
            ])
            ->groupBy('date')
            ->toArray();
    }
}
