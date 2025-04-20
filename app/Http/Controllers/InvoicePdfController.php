<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\GeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

use function Filament\authorize;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice): Response
    {
        authorize('view', $invoice);

        $invoice->load(['contract.customer', 'contract.supplier.bankAccount']);

        $supplier = $invoice->contract->supplier;
        $customer = $invoice->contract->customer;
        $bank = $invoice->contract->supplier->bankAccount;

        // Get invoice task hours
        $invoiceTaskHours = $invoice->taskHours()->with('task')->latest('date')->get();

        // Calculate invoice total hours
        $invoiceTotalHours = $invoiceTaskHours->sum('hours');

        // Calculate invoice total amount
        $invoiceTotalAmount = $invoiceTotalHours * $invoice->contract->price_per_hour;

        $data = [
            'supplier' => [
                'name' => $supplier->name,
                'address1' => $supplier->address->street,
                'address2' => "{$supplier->address->zip}, {$supplier->address->city}",
                'address3' => $supplier->address->country->countryName(),
                'vat' => $supplier->vat_number,
                'registration' => $supplier->registration_number,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
            ],

            'customer' => [
                'name' => $customer->name,
                'address1' => $customer->address->street,
                'address2' => "{$customer->address->zip}, {$customer->address->city}",
                'address3' => $customer->address->country->countryName(),
                'vat' => $customer->vat_number,
                'registration' => $customer->registration_number,
            ],

            'invoice' => [
                'number' => $invoice->number,
                'date' => $invoice->created_at->format('Y-m-d'),
                'dueDate' => $invoice->due_date->format('Y-m-d'),
                'unit_price' => $invoice->contract->price_per_hour,
                'totalHours' => $invoiceTotalHours,
                'subtotal' => $invoiceTotalAmount,
                'totalAmount' => $invoiceTotalAmount,
                'items' => $invoiceTaskHours,
                'isReverseCharge' => $invoice->contract->reverse_charge,
                'currency' => $invoice->contract->currency->getCurrencySymbol(),
            ],

            'bank' => [
                'name' => $bank->bank_name,
                'code' => $bank->bank_code,
                'account' => $bank->account_number,
                'iban' => $bank->iban,
                'swift' => $bank->swift,
                'paymentReference' => $invoice->number,
            ],
        ];

        $pdf = PDF::loadView('invoice-pdf', $data);

        $fileName = GeneratorService::generateFileName(['invoice', $invoice->number]);

        return $pdf->stream($fileName);

    }
}
