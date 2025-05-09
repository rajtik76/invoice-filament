<?php

use App\Filament\Resources\SupplierResource\Pages\ListSuppliers;
use App\Models\Address;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\User;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can see suppliers', function () {
    $suppliers = Supplier::factory()
        ->count(5)
        ->recycle($this->user)
        ->create();

    $otherUserSupplier = Supplier::factory()
        ->count(5)
        ->create();

    livewire(ListSuppliers::class)
        ->assertCanSeeTableRecords($suppliers) // can see current user records
        ->assertCanNotSeeTableRecords($otherUserSupplier); // but can't see other user records
});

it('can create supplier', function () {
    $address = Address::factory()
        ->recycle($this->user)
        ->create(['id' => 100]);

    $bankAccount = BankAccount::factory()
        ->recycle($this->user)
        ->create(['id' => 200]);

    $supplierData = [
        'name' => 'New name',
        'email' => 'new@test.local',
        'phone' => '111222333',
        'registration_number' => '555666777',
        'vat_number' => 'CS999888777',
        'address_id' => $address->id,
        'bank_account_id' => $bankAccount->id,
    ];

    livewire(ListSuppliers::class)
        ->callAction(CreateAction::class, $supplierData)
        ->assertHasNoTableActionErrors();

    assertDatabaseHas('suppliers', $supplierData);
});

it('can edit supplier', function () {
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
    $supplierNewData = [
        'name' => 'New name',
        'email' => 'new@test.local',
        'phone' => '111222333',
        'registration_number' => '555666777',
        'vat_number' => 'CS999888777',
        'address_id' => $address->id,
        'bank_account_id' => $bankAccount->id,
    ];

    livewire(ListSuppliers::class)
        ->callTableAction(name: EditAction::class, record: $supplier, data: $supplierNewData)
        ->assertHasNoTableActionErrors();

    assertDatabaseHas('suppliers', $supplierNewData);
});

it('can bulk delete suppliers', function () {
    $suppliers = Supplier::factory()
        ->count(10)
        ->recycle($this->user)
        ->create();

    // Other user suppliers
    Supplier::factory()->count(10)->create();

    livewire(ListSuppliers::class)
        ->callTableBulkAction(DeleteBulkAction::class, $suppliers);

    foreach ($suppliers as $post) {
        $this->assertModelMissing($post);
    }

    // Other user suppliers must remain still
    assertDatabaseCount('suppliers', 10);
});
