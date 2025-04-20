<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\TaskHour;
use App\Services\GeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportPdfController extends Controller
{
    public function __invoke(Invoice $invoice, Request $request): Response
    {
        $request->user()->can('view', $invoice);

        /** @var Collection<int, TaskHour> $taskHours */
        $taskHours = $invoice->taskHours()->with('task')->get();

        $data = [
            'contract' => $invoice->contract,
            'year' => $invoice->issue_date->year,
            'month' => $invoice->issue_date->month,
            'totalHours' => $invoice->taskHours()->sum('hours'),
            'taskHoursGroupedByDate' => $taskHours
                ->groupBy(fn (TaskHour $taskHour): string => $taskHour->date->format('Y-m-d'))
                ->sortKeys(),
        ];

        $pdf = PDF::loadView('report-pdf', $data);

        $filename = GeneratorService::generateFileName(['report', $invoice->contract->name, sprintf('%04d', $invoice->issue_date->year), sprintf('%02d', $invoice->issue_date->month)]);

        return $pdf->stream($filename);
    }
}
