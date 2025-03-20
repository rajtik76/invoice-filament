<?php

use App\Http\Controllers\InvoicePdfController;
use Illuminate\Support\Facades\Route;

Route::get('/invoice/pdf/{invoice}', InvoicePdfController::class)->name('invoice.pdf');
