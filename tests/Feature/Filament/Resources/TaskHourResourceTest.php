<?php
declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\InvoiceStatusEnum;
use App\Filament\Resources\TaskHourResource\Pages\ListTaskHours;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\Task;
use App\Models\TaskHour;
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

    // Create required relationships for the tests
    $this->customer = Customer::factory()->recycle($this->user)->create();
    $this->supplier = Supplier::factory()->recycle($this->user)->create();
    $this->contract = Contract::factory()
        ->recycle([$this->user, $this->customer, $this->supplier])
        ->create();
    $this->task = Task::factory()
        ->recycle([$this->user, $this->contract])
        ->active()
        ->create();
});

it('can see task hours', function () {
    // Create task hours for the current user
    $taskHours = TaskHour::factory()
        ->count(5)
        ->recycle([$this->user, $this->task])
        ->create();

    // Create task hours for another user
    $otherUser = User::factory()->create();
    $otherCustomer = Customer::factory()->recycle($otherUser)->create();
    $otherSupplier = Supplier::factory()->recycle($otherUser)->create();
    $otherContract = Contract::factory()
        ->recycle([$otherUser, $otherCustomer, $otherSupplier])
        ->create();
    $otherTask = Task::factory()
        ->recycle([$otherUser, $otherContract])
        ->create();

    $otherUserTaskHours = TaskHour::factory()
        ->count(5)
        ->recycle([$otherUser, $otherTask])
        ->create();

    // Test that the user can see their own task hours but not others'
    livewire(ListTaskHours::class)
        ->assertCanSeeTableRecords($taskHours) // can see current user records
        ->assertCanNotSeeTableRecords($otherUserTaskHours); // but can't see other user records
});

it('can create task hour', function () {
    // Prepare task hour data
    $taskHourData = [
        'task_id' => $this->task->id,
        'date' => now()->startOfDay()->format('Y-m-d H:i:s'),
        'hours' => 2.5,
        'comment' => 'This is a test task hour',
    ];

    // Test creating a new task hour
    livewire(ListTaskHours::class)
        ->callAction(CreateAction::class, $taskHourData)
        ->assertHasNoActionErrors();

    // Verify the task hour was created in the database
    assertDatabaseHas('task_hours', array_merge(
        $taskHourData,
        ['user_id' => $this->user->id]
    ));
});

it('can edit task hour', function () {
    // Create a task hour to edit
    $taskHour = TaskHour::factory()
        ->recycle([$this->user, $this->task])
        ->create([
            'date' => now()->subDays(5),
            'hours' => 1.5,
            'comment' => 'Old comment',
        ]);

    // New data for the task hour
    $newTaskHourData = [
        'task_id' => $this->task->id,
        'date' => now()->startOfDay()->format('Y-m-d H:i:s'),
        'hours' => 3.5,
        'comment' => 'New comment',
    ];

    // Test editing the task hour
    livewire(ListTaskHours::class)
        ->callTableAction(EditAction::class, $taskHour, $newTaskHourData)
        ->assertHasNoTableActionErrors();

    // Verify the task hour was updated in the database
    assertDatabaseHas('task_hours', array_merge(
        $newTaskHourData,
        ['id' => $taskHour->id, 'user_id' => $this->user->id]
    ));
});

it('can filter task hours by task', function () {
    // Create another task
    $anotherTask = Task::factory()
        ->recycle([$this->user, $this->contract])
        ->create();

    // Create task hours for the first task
    $firstTaskHours = TaskHour::factory()
        ->count(3)
        ->recycle([$this->user, $this->task])
        ->create();

    // Create task hours for the second task
    $secondTaskHours = TaskHour::factory()
        ->count(3)
        ->recycle([$this->user, $anotherTask])
        ->create();

    // Test filtering by the first task
    livewire(ListTaskHours::class)
        ->filterTable('task', $this->task->id)
        ->assertCanSeeTableRecords($firstTaskHours)
        ->assertCanNotSeeTableRecords($secondTaskHours);

    // Test filtering by the second task
    livewire(ListTaskHours::class)
        ->filterTable('task', $anotherTask->id)
        ->assertCanSeeTableRecords($secondTaskHours)
        ->assertCanNotSeeTableRecords($firstTaskHours);

    // Test removing the filter to see all task hours
    livewire(ListTaskHours::class)
        ->assertCanSeeTableRecords($firstTaskHours)
        ->assertCanSeeTableRecords($secondTaskHours);
});

it('can filter task hours by invoice status', function () {
    // Create a draft invoice
    $draftInvoice = Invoice::factory()
        ->recycle([$this->user, $this->contract])
        ->create(['status' => InvoiceStatusEnum::Draft]);

    // Create task hours without invoice
    $taskHoursWithoutInvoice = TaskHour::factory()
        ->count(3)
        ->recycle([$this->user, $this->task])
        ->create();

    // Create task hours with invoice
    $taskHoursWithInvoice = TaskHour::factory()
        ->count(3)
        ->recycle([$this->user, $this->task])
        ->create();

    // Attach task hours to an invoice
    foreach ($taskHoursWithInvoice as $taskHour) {
        $draftInvoice->invoiceHours()->create(['task_hour_id' => $taskHour->id]);
    }

    // Test filtering for task hours with an invoice
    livewire(ListTaskHours::class)
        ->filterTable('invoice', true)
        ->assertCanSeeTableRecords($taskHoursWithInvoice)
        ->assertCanNotSeeTableRecords($taskHoursWithoutInvoice);

    // Test filtering for task hours without an invoice
    livewire(ListTaskHours::class)
        ->filterTable('invoice', false)
        ->assertCanSeeTableRecords($taskHoursWithoutInvoice)
        ->assertCanNotSeeTableRecords($taskHoursWithInvoice);

    // Test removing the filter to see all task hours
    livewire(ListTaskHours::class)
        ->removeTableFilter('invoice')
        ->assertCanSeeTableRecords($taskHoursWithoutInvoice)
        ->assertCanSeeTableRecords($taskHoursWithInvoice);
});

it('can bulk delete task hours', function () {
    // Create task hours for the current user
    $taskHours = TaskHour::factory()
        ->count(10)
        ->recycle([$this->user, $this->task])
        ->create();

    // Create task hours for another user
    $otherUser = User::factory()->create();
    $otherCustomer = Customer::factory()->recycle($otherUser)->create();
    $otherSupplier = Supplier::factory()->recycle($otherUser)->create();
    $otherContract = Contract::factory()
        ->recycle([$otherUser, $otherCustomer, $otherSupplier])
        ->create();
    $otherTask = Task::factory()
        ->recycle([$otherUser, $otherContract])
        ->create();

    TaskHour::factory()
        ->count(10)
        ->recycle([$otherUser, $otherContract])
        ->create();

    // Test bulk deleting task hours
    livewire(ListTaskHours::class)
        ->callTableBulkAction(DeleteBulkAction::class, $taskHours);

    // Verify all selected task hours were deleted
    foreach ($taskHours as $taskHour) {
        $this->assertModelMissing($taskHour);
    }

    // Verify other user task hours remain
    assertDatabaseCount('task_hours', 10);
});

it('can add task hours to invoice', function () {
    // Create a draft invoice
    $draftInvoice = Invoice::factory()
        ->recycle([$this->user, $this->contract])
        ->create(['status' => InvoiceStatusEnum::Draft]);

    // Create task hours
    $taskHours = TaskHour::factory()
        ->count(5)
        ->recycle([$this->user, $this->task])
        ->create();

    // Test adding task hours to invoice
    livewire(ListTaskHours::class)
        ->callTableBulkAction('add_to_invoice', $taskHours, ['invoice_id' => $draftInvoice->id]);

    // Verify all selected task hours were added to the invoice
    foreach ($taskHours as $taskHour) {
        $this->assertDatabaseHas('invoice_hours', [
            'invoice_id' => $draftInvoice->id,
            'task_hour_id' => $taskHour->id,
        ]);
    }
});
