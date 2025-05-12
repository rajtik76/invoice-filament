<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Enums\LocaleEnum;
use App\Models\Invoice;
use App\ValueObject\InvoiceSettingsValueObject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

use function Pest\Laravel\actingAs;

it('use invoice settings locale and reverse charge during PDF generation', function () {
    $invoice = Invoice::factory()->create(['settings' => new InvoiceSettingsValueObject(
        reverseCharge: true,
        invoiceLocale: LocaleEnum::Czech,
    )]);

    Pdf::shouldReceive('loadView')
        ->once()
        ->withArgs(function (string $view, array $data) {
            return app()->getLocale() === LocaleEnum::Czech->value
                && data_get($data, 'invoice.isReverseCharge') === true;
        })
        ->andReturnSelf();

    Pdf::shouldReceive('stream')
        ->once()
        ->andReturn(new Response);

    actingAs($invoice->user)->get(route('invoice.pdf', $invoice))
        ->assertOk();
});
