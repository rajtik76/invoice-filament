<?php
declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages\ListBankAccounts;
use App\Models\BankAccount;
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

it('can see bank accounts', function () {
    // Create bank accounts for the current user
    $bankAccounts = BankAccount::factory()
        ->count(5)
        ->recycle($this->user)
        ->create();

    // Create bank accounts for another user
    $otherUserBankAccounts = BankAccount::factory()
        ->count(5)
        ->create();

    // Test that the user can see their own bank accounts but not others'
    livewire(ListBankAccounts::class)
        ->assertCanSeeTableRecords($bankAccounts) // can see current user records
        ->assertCanNotSeeTableRecords($otherUserBankAccounts); // but can't see other user records
});

it('can create bank account', function () {
    // Prepare bank account data
    $bankAccountData = [
        'bank_name' => 'Test Bank',
        'account_number' => '1234567890',
        'bank_code' => '0800',
        'iban' => 'CZ1234567890',
        'swift' => 'TESTSWIFT',
    ];

    // Test creating a new bank account
    livewire(ListBankAccounts::class)
        ->callAction(CreateAction::class, $bankAccountData)
        ->assertHasNoActionErrors();

    // Verify the bank account was created in the database
    assertDatabaseHas('bank_accounts', array_merge(
        $bankAccountData,
        ['user_id' => $this->user->id]
    ));
});

it('can edit bank account', function () {
    // Create a bank account to edit
    $bankAccount = BankAccount::factory()
        ->recycle($this->user)
        ->create([
            'bank_name' => 'Old Bank',
            'account_number' => '9876543210',
            'bank_code' => '0100',
            'iban' => 'CZ9876543210',
            'swift' => 'OLDSWIFT',
        ]);

    // New data for the bank account
    $newBankAccountData = [
        'bank_name' => 'New Bank',
        'account_number' => '1234567890',
        'bank_code' => '0800',
        'iban' => 'CZ1234567890',
        'swift' => 'NEWSWIFT',
    ];

    // Test editing the bank account
    livewire(ListBankAccounts::class)
        ->callTableAction(EditAction::class, $bankAccount, $newBankAccountData)
        ->assertHasNoTableActionErrors();

    // Verify the bank account was updated in the database
    assertDatabaseHas('bank_accounts', array_merge(
        $newBankAccountData,
        ['id' => $bankAccount->id, 'user_id' => $this->user->id]
    ));
});

it('can bulk delete bank accounts', function () {
    // Create bank accounts for the current user
    $bankAccounts = BankAccount::factory()
        ->count(10)
        ->recycle($this->user)
        ->create();

    // Create bank accounts for another user
    BankAccount::factory()
        ->count(10)
        ->create();

    // Test bulk deleting bank accounts
    livewire(ListBankAccounts::class)
        ->callTableBulkAction(DeleteBulkAction::class, $bankAccounts);

    // Verify all selected bank accounts were deleted
    foreach ($bankAccounts as $bankAccount) {
        $this->assertModelMissing($bankAccount);
    }

    // Verify other user bank accounts remain
    assertDatabaseCount('bank_accounts', 10);
});
