<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Task;
use App\Models\TaskHour;
use App\Services\GeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Fluent;

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
        $invoiceTaskHours = $invoice->taskHours()
            ->with('task')
            ->get()
            ->groupBy('task_id')
            ->map(function (/** @var Collection<TaskHour> $group */ Collection $group): Fluent {
                /** @var TaskHour $taskHour */
                $taskHour = $group->first();
                $task = $taskHour->task;

                return new Fluent([
                    'task' => $task,
                    'task_name' => $task->name,
                    'hours' => $group->sum('hours'),
                ]);
            })
            ->sortBy('task_name');

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
                'date' => $invoice->issue_date->format('Y-m-d'),
                'dueDate' => $invoice->due_date->format('Y-m-d'),
                'unit_price' => $invoice->contract->price_per_hour,
                'totalHours' => $invoiceTotalHours,
                'subtotal' => $invoiceTotalAmount,
                'totalAmount' => $invoiceTotalAmount,
                'items' => $invoiceTaskHours,
                'isReverseCharge' => $invoice->settings->reverseCharge,
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

        // Get current locale
        $locale = app()->getLocale();

        // Set locale from invoice settings
        app()->setLocale($invoice->settings->invoiceLocale->value);

        // Generate invoice PDF
        $pdf = PDF::loadView('invoice-pdf', $data);

        // Restore locale
        app()->setLocale($locale);

        $fileName = GeneratorService::generateFileName(['invoice', $invoice->number]);

        return $pdf->stream($fileName);

    }
}
