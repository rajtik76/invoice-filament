<?php
declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\InvoiceStatusEnum;
use App\Filament\Resources\InvoiceResource\Pages\ListInvoices;
use App\Models\Invoice;
use App\Models\User;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can delete invoice', function () {
    $invoice = Invoice::factory()
        ->recycle($this->user)
        ->create();

    livewire(ListInvoices::class)
        ->callTableAction(DeleteAction::class, $invoice);

    $this->assertModelMissing($invoice);
});

it('can filter invoices by `status`', function () {
    $invoices = Invoice::factory()
        ->count(4)
        ->recycle($this->user)
        ->state(new Sequence(
            ['status' => InvoiceStatusEnum::Draft],
            ['status' => InvoiceStatusEnum::Draft],
            ['status' => InvoiceStatusEnum::Issued],
            ['status' => InvoiceStatusEnum::Issued],
        ))
        ->create();

    // Assert can see only filtered issued invoices
    livewire(ListInvoices::class)
        ->assertCanSeeTableRecords($invoices)
        ->filterTable('status', InvoiceStatusEnum::Issued->value)
        ->assertCanSeeTableRecords($invoices->where('status', InvoiceStatusEnum::Issued))
        ->assertCanNotSeeTableRecords($invoices->where('status', InvoiceStatusEnum::Draft));

    // Assert can see only filtered drafted invoices
    livewire(ListInvoices::class)
        ->assertCanSeeTableRecords($invoices)
        ->filterTable('status', InvoiceStatusEnum::Draft->value)
        ->assertCanSeeTableRecords($invoices->where('status', InvoiceStatusEnum::Draft))
        ->assertCanNotSeeTableRecords($invoices->where('status', InvoiceStatusEnum::Issued));

    // Assert can see all invoices
    livewire(ListInvoices::class)
        ->assertCanSeeTableRecords($invoices)
        ->filterTable('status')
        ->assertCanSeeTableRecords($invoices);
});

