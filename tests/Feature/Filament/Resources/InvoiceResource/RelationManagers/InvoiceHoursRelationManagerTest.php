<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\InvoiceResource\RelationManagers;

use App\Filament\Resources\InvoiceResource\RelationManagers\InvoiceHoursRelationManager;
use App\Models\Invoice;

it('relation manager is readonly for issued invoice', function () {
    $invoice = Invoice::factory()->issued()->create();

    $relationManager = new InvoiceHoursRelationManager;

    $relationManager->ownerRecord = $invoice;

    expect($relationManager->isReadOnly())->toBeTrue();
});
