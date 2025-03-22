<?php

use App\Filament\Resources\SupplierResource\Pages\ListSuppliers;
use App\Models\Address;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\User;
use Filament\Tables\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can edit supplier', function () {

    $address = Address::factory()
        ->recycle($this->user)
        ->create(['id' => 100]);
    $bankAccount = BankAccount::factory()
        ->recycle($this->user)
        ->create(['id' => 200]);

    $supplier = Supplier::factory()
        ->recycle($this->user)
        ->create([
            'name' => 'Joe Doe',
            'email' => 'unit@test.local',
            'phone' => '1234567890',
            'registration_number' => '9876543210',
            'vat_number' => 'DE123456789',
        ]);

    livewire(ListSuppliers::class)
        ->mountTableAction(EditAction::class, $supplier)
        ->assertTableActionDataSet([
            'name' => 'Joe Doe',
            'email' => 'unit@test.local',
            'phone' => '1234567890',
            'registration_number' => '9876543210',
            'vat_number' => 'DE123456789',
            'address_id' => 101,
            'bank_account_id' => 201,
        ])
        ->setTableActionData([
            'name' => 'New name',
            'email' => 'new@test.local',
            'phone' => '111222333',
            'registration_number' => '555666777',
            'vat_number' => 'CS999888777',
            'address_id' => 100,
            'bank_account_id' => 200,
        ])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    expect($supplier->refresh())
        ->name->toBe('New name')
        ->email->toBe('new@test.local')
        ->phone->toBe('111222333')
        ->registration_number->toBe('555666777')
        ->vat_number->toBe('CS999888777')
        ->address_id->toBe(100)
        ->bank_account_id->toBe(200);
});
