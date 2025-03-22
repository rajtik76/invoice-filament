<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\GeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportPdfController extends Controller
{
    public function __invoke(Report $report, Request $request): Response
    {
        $request->user()->can('view', $report);

        $pdf = PDF::loadView('report-pdf', $report->load(['contract.customer'])->toArray());

        $filename = GeneratorService::generateFileName(['report', $report->contract->customer->name, sprintf('%04d', $report->year), sprintf('%02d', $report->month)]);

        return $pdf->stream($filename);
    }
}
