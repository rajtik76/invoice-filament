<?php

use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\ReportPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/invoice/pdf/{invoice}', InvoicePdfController::class)->name('invoice.pdf');
Route::get('/report/pdf/{report}', ReportPdfController::class)->name('report.pdf');
