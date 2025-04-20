<?php

use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\ReportPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/invoice/{invoice}/pdf', InvoicePdfController::class)->name('invoice.pdf');
Route::get('/invoice/{invoice}/report/pdf', ReportPdfController::class)->name('invoice.report.pdf');
