<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use function Filament\authorize;

class ReportPdfController extends Controller
{
    public function __invoke(Report $report): Response
    {
        authorize('view', $report);

        $data = [
            'year' => $report->year,
            'month' => $report->month,
            'customer' => $report->contract->customer,
            'spend_hours' => $report->content,
        ];

        $pdf = PDF::loadView('report-pdf', $data);

        return $pdf->stream('report.pdf');
    }
}
