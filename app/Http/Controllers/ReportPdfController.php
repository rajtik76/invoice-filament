<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportPdfController extends Controller
{
    public function __invoke(Report $report, Request $request): Response
    {
        $request->user()->can('view', $report);

        $pdf = PDF::loadView('report-pdf', $report->load(['contract.customer'])->toArray());

        return $pdf->stream('report.pdf');
    }
}
