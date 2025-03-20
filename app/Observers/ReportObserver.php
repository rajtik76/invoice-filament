<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Report;
use App\Services\ReportService;

class ReportObserver
{
    public function created(Report $report): void
    {
        $this->updateContent($report);
    }

    public function updated(Report $report): void
    {
        if ($report->isDirty(['contract_id', 'year', 'month'])) {
            $this->updateContent($report);
        }
    }

    public function deleted(Report $report): void {}

    public function restored(Report $report): void {}

    public function forceDeleted(Report $report): void {}

    private function updateContent(Report $report): void
    {
        $report->updateQuietly(['content' => ReportService::createContent(
            contract: $report->contract,
            year: intval($report->year),
            month: intval($report->month)
        )]);
    }
}
