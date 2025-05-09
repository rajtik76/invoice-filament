<?php
declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\CurrencyEnum;
use App\Filament\Resources\ContractResource\Pages\ListContracts;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Set up an authenticated user for each test
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create a customer and supplier for the tests
    $this->customer = Customer::factory()->recycle($this->user)->create();
    $this->supplier = Supplier::factory()->recycle($this->user)->create();
});

it('can see contracts', function () {
    // Create contracts for the current user
    $contracts = Contract::factory()
        ->count(5)
        ->recycle($this->user)
        ->recycle($this->customer)
        ->recycle($this->supplier)
        ->create();

    // Create contracts for another user
    $otherUser = User::factory()->create();
    $otherCustomer = Customer::factory()->recycle($otherUser)->create();
    $otherSupplier = Supplier::factory()->recycle($otherUser)->create();

    $otherUserContracts = Contract::factory()
        ->count(5)
        ->recycle($otherUser)
        ->recycle($otherCustomer)
        ->recycle($otherSupplier)
        ->create();

    // Test that the user can see their own contracts but not others'
    livewire(ListContracts::class)
        ->assertCanSeeTableRecords($contracts) // can see current user records
        ->assertCanNotSeeTableRecords($otherUserContracts); // but can't see other user records
});

it('can create contract', function () {
    // Prepare contract data
    $contractData = [
        'customer_id' => $this->customer->id,
        'supplier_id' => $this->supplier->id,
        'name' => 'Test Contract',
        'signed_at' => now()->format('Y-m-d'),
        'price_per_hour' => 100,
        'currency' => CurrencyEnum::EUR,
        'reverse_charge' => false,
        'active' => true,
    ];

    // Test creating a new contract
    livewire(ListContracts::class)
        ->callAction(CreateAction::class, $contractData)
        ->assertHasNoActionErrors();

    // Verify the contract was created in the database
    assertDatabaseHas('contracts', [
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'supplier_id' => $this->supplier->id,
        'name' => 'Test Contract',
        'signed_at' => now()->startOfDay()->format('Y-m-d H:i:s'),
        'price_per_hour' => 100,
        'currency' => CurrencyEnum::EUR,
        'reverse_charge' => false,
        'active' => true,
    ]);
});

it('can edit contract', function () {
    // Create a contract to edit
    $contract = Contract::factory()
        ->recycle($this->user)
        ->recycle($this->customer)
        ->recycle($this->supplier)
        ->create([
            'name' => 'Old Contract',
            'price_per_hour' => 50,
            'currency' => CurrencyEnum::CZK,
            'active' => false,
        ]);

    // New data for the contract
    $newContractData = [
        'customer_id' => $this->customer->id,
        'supplier_id' => $this->supplier->id,
        'name' => 'New Contract',
        'signed_at' => now()->format('Y-m-d'),
        'price_per_hour' => 150,
        'currency' => CurrencyEnum::EUR,
        'reverse_charge' => true,
        'active' => true,
    ];

    // Test editing the contract
    livewire(ListContracts::class)
        ->callTableAction(EditAction::class, $contract, $newContractData)
        ->assertHasNoTableActionErrors();

    // Verify the contract was updated in the database
    assertDatabaseHas('contracts', [
        'id' => $contract->id,
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'supplier_id' => $this->supplier->id,
        'name' => 'New Contract',
        'signed_at' => now()->startOfDay()->format('Y-m-d H:i:s'),
        'price_per_hour' => 150,
        'currency' => CurrencyEnum::EUR,
        'reverse_charge' => true,
        'active' => true,
    ]);
});

it('can bulk delete contracts', function () {
    // Create contracts for the current user
    $contracts = Contract::factory()
        ->count(10)
        ->recycle($this->user)
        ->recycle($this->customer)
        ->recycle($this->supplier)
        ->create();

    // Create contracts for another user
    $otherUser = User::factory()->create();
    $otherCustomer = Customer::factory()->recycle($otherUser)->create();
    $otherSupplier = Supplier::factory()->recycle($otherUser)->create();

    Contract::factory()
        ->count(10)
        ->recycle($otherUser)
        ->recycle($otherCustomer)
        ->recycle($otherSupplier)
        ->create();

    // Test bulk deleting contracts
    livewire(ListContracts::class)
        ->callTableBulkAction(DeleteBulkAction::class, $contracts);

    // Verify all selected contracts were deleted
    foreach ($contracts as $contract) {
        $this->assertModelMissing($contract);
    }

    // Verify other user contracts remain
    assertDatabaseCount('contracts', 10);
});
