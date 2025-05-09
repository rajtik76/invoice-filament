<?php
declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Set up an authenticated user for each test
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create an address for the tests
    $this->address = Address::factory()->recycle($this->user)->create();
});

it('can see customers', function () {
    // Create customers for the current user
    $customers = Customer::factory()
        ->count(5)
        ->recycle($this->user)
        ->recycle($this->address)
        ->create();

    // Create customers for another user
    $otherUser = User::factory()->create();
    $otherAddress = Address::factory()->recycle($otherUser)->create();

    $otherUserCustomers = Customer::factory()
        ->count(5)
        ->recycle($otherUser)
        ->recycle($otherAddress)
        ->create();

    // Test that the user can see their own customers but not others'
    livewire(ListCustomers::class)
        ->assertCanSeeTableRecords($customers) // can see current user records
        ->assertCanNotSeeTableRecords($otherUserCustomers); // but can't see other user records
});

it('can create customer', function () {
    // Prepare customer data
    $customerData = [
        'address_id' => $this->address->id,
        'name' => 'Test Customer',
        'registration_number' => '12345678',
        'vat_number' => 'CZ12345678',
    ];

    // Test creating a new customer
    livewire(ListCustomers::class)
        ->callAction(CreateAction::class, $customerData)
        ->assertHasNoActionErrors();

    // Verify the customer was created in the database
    assertDatabaseHas('customers', array_merge(
        $customerData,
        ['user_id' => $this->user->id]
    ));
});

it('can edit customer', function () {
    // Create a customer to edit
    $customer = Customer::factory()
        ->recycle($this->user)
        ->recycle($this->address)
        ->create([
            'name' => 'Old Customer',
            'registration_number' => '87654321',
            'vat_number' => 'CZ87654321',
        ]);

    // New data for the customer
    $newCustomerData = [
        'address_id' => $this->address->id,
        'name' => 'New Customer',
        'registration_number' => '12345678',
        'vat_number' => 'CZ12345678',
    ];

    // Test editing the customer
    livewire(ListCustomers::class)
        ->callTableAction(EditAction::class, $customer, $newCustomerData)
        ->assertHasNoTableActionErrors();

    // Verify the customer was updated in the database
    assertDatabaseHas('customers', array_merge(
        $newCustomerData,
        ['id' => $customer->id, 'user_id' => $this->user->id]
    ));
});

it('can bulk delete customers', function () {
    // Create customers for the current user
    $customers = Customer::factory()
        ->count(10)
        ->recycle($this->user)
        ->recycle($this->address)
        ->create();

    // Create customers for another user
    $otherUser = User::factory()->create();
    $otherAddress = Address::factory()->recycle($otherUser)->create();

    Customer::factory()
        ->count(10)
        ->recycle($otherUser)
        ->recycle($otherAddress)
        ->create();

    // Test bulk deleting customers
    livewire(ListCustomers::class)
        ->callTableBulkAction(DeleteBulkAction::class, $customers);

    // Verify all selected customers were deleted
    foreach ($customers as $customer) {
        $this->assertModelMissing($customer);
    }

    // Verify other user customers remain
    assertDatabaseCount('customers', 10);
});
