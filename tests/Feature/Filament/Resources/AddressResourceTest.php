<?php
declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\CountryEnum;
use App\Filament\Resources\AddressResource\Pages\ListAddresses;
use App\Models\Address;
use App\Models\User;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Setup authenticated user for each test
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can see addresses', function () {
    // Create addresses for the current user
    $addresses = Address::factory()
        ->count(5)
        ->recycle($this->user)
        ->create();

    // Create addresses for another user
    $otherUserAddresses = Address::factory()
        ->count(5)
        ->create();

    // Test that the user can see their own addresses but not others'
    livewire(ListAddresses::class)
        ->assertCanSeeTableRecords($addresses) // can see current user records
        ->assertCanNotSeeTableRecords($otherUserAddresses); // but can't see other user records
});

it('can create address', function () {
    // Prepare address data
    $addressData = [
        'street' => '123 Main St',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => CountryEnum::Czech->value,
    ];

    // Test creating a new address
    livewire(ListAddresses::class)
        ->callAction(CreateAction::class, $addressData)
        ->assertHasNoActionErrors();

    // Verify the address was created in the database
    assertDatabaseHas('addresses', array_merge(
        $addressData,
        ['user_id' => $this->user->id]
    ));
});

it('can edit address', function () {
    // Create an address to edit
    $address = Address::factory()
        ->recycle($this->user)
        ->create([
            'street' => 'Old Street',
            'city' => 'Old City',
            'zip' => '54321',
            'country' => CountryEnum::Germany,
        ]);

    // New data for the address
    $newAddressData = [
        'street' => 'New Street',
        'city' => 'New City',
        'zip' => '12345',
        'country' => CountryEnum::Czech->value,
    ];

    // Test editing the address
    livewire(ListAddresses::class)
        ->callTableAction(EditAction::class, $address, $newAddressData)
        ->assertHasNoTableActionErrors();

    // Verify the address was updated in the database
    assertDatabaseHas('addresses', array_merge(
        $newAddressData,
        ['id' => $address->id, 'user_id' => $this->user->id]
    ));
});

it('can bulk delete addresses', function () {
    // Create addresses for the current user
    $addresses = Address::factory()
        ->count(10)
        ->recycle($this->user)
        ->create();

    // Create addresses for another user
    Address::factory()
        ->count(10)
        ->create();

    // Test bulk deleting addresses
    livewire(ListAddresses::class)
        ->callTableBulkAction(DeleteBulkAction::class, $addresses);

    // Verify all selected addresses were deleted
    foreach ($addresses as $address) {
        $this->assertModelMissing($address);
    }

    // Verify other user addresses remain
    assertDatabaseCount('addresses', 10);
});
