<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\InvoiceStatusEnum;
use App\Enums\LocaleEnum;
use App\Filament\Resources\InvoiceResource\Pages\EditInvoice;
use App\Filament\Resources\InvoiceResource\Pages\ListInvoices;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use App\ValueObject\ContractSettingsValueObject;
use App\ValueObject\InvoiceSettingsValueObject;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Factories\Sequence;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can see invoices', function () {
    $invoices = Invoice::factory()
        ->count(5)
        ->recycle($this->user)
        ->create();

    $otherUserInvoices = Invoice::factory()
        ->count(5)
        ->create();

    livewire(ListInvoices::class)
        ->assertCanSeeTableRecords($invoices) // can see current user records
        ->assertCanNotSeeTableRecords($otherUserInvoices); // but can't see other user records
});

it('can create invoice', function () {
    $contract = Contract::factory()
        ->recycle($this->user)
        ->create(['id' => 100]);

    $invoiceData = [
        'contract_id' => $contract->id,
        'number' => 'INV-2023-001',
        'prepare_hours' => false,
        'settings' => [
            'invoice_locale' => LocaleEnum::Czech,
            'reverse_charge' => true,
        ],
    ];

    livewire(ListInvoices::class)
        ->callAction(CreateAction::class, $invoiceData)
        ->assertHasNoActionErrors();

    assertDatabaseHas('invoices', [
        'user_id' => $this->user->id,
        'contract_id' => $contract->id,
        'number' => 'INV-2023-001',
        'status' => InvoiceStatusEnum::Draft->value,
        'settings->invoiceLocale' => LocaleEnum::Czech,
        'settings->reverseCharge' => true,
    ]);
});

it('can edit invoice', function () {
    $invoice = Invoice::factory()
        ->recycle($this->user)
        ->draft()
        ->create([
            'number' => 'INV-2023-001',
            'settings' => new InvoiceSettingsValueObject(reverseCharge: false, invoiceLocale: LocaleEnum::English),
        ]);

    $invoiceNewData = [
        'number' => 'INV-2023-002',
        'settings' => [
            'invoice_locale' => LocaleEnum::Czech->value,
            'reverse_charge' => true,
        ],
    ];

    livewire(EditInvoice::class, ['record' => $invoice->getKey()])
        ->fillForm($invoiceNewData)
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'number' => 'INV-2023-002',
        'settings->invoiceLocale' => LocaleEnum::Czech,
        'settings->reverseCharge' => true,
    ]);
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

it('create invoice with settings inherited from contract', function () {
    // Contract with specific settings
    $contract = Contract::factory()
        ->recycle($this->user)
        ->create([
            'id' => 100,
            'settings' => new ContractSettingsValueObject(reverseCharge: true, invoiceLocale: LocaleEnum::Czech),
        ]);

    livewire(ListInvoices::class)
        ->callAction(CreateAction::class, ['contract_id' => $contract->id, 'number' => 'TEST-12345'])
        ->assertHasNoActionErrors();

    assertDatabaseHas('invoices', [
        'user_id' => $this->user->id,
        'contract_id' => $contract->id,
        'number' => 'TEST-12345',
        'settings->invoiceLocale' => LocaleEnum::Czech, // settings from contract
        'settings->reverseCharge' => true, // settings from contract
    ]);
});

it('can edit settings of issued test', function () {
    $invoice = Invoice::factory()
        ->recycle($this->user)
        ->issued()
        ->create([
            'number' => 'TEST-12345',
            'settings' => new InvoiceSettingsValueObject(reverseCharge: false, invoiceLocale: LocaleEnum::English),
        ]);

    $invoiceNewData = [
        'settings' => [
            'invoice_locale' => LocaleEnum::Czech->value,
            'reverse_charge' => true,
        ],
    ];

    livewire(EditInvoice::class, ['record' => $invoice->getKey()])
        ->fillForm($invoiceNewData)
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'contract_id' => $invoice->contract_id,
        'number' => 'TEST-12345',
        'settings->invoiceLocale' => LocaleEnum::Czech,
        'settings->reverseCharge' => true,
    ]);
});
