<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\GeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportPdfController extends Controller
{
    public function __invoke(Invoice $invoice, Request $request): Response
    {
        $request->user()->can('view', $invoice);

        $pdf = PDF::loadView('report-pdf', $invoice->load(['contract.customer'])->toArray());

        $filename = GeneratorService::generateFileName(['report', $invoice->contract->name, sprintf('%04d', $invoice->issue_date->year), sprintf('%02d', $invoice->issue_date->month)]);

        return $pdf->stream($filename);
    }
}
