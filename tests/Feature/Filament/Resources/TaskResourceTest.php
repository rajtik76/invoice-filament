<?php
declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages\ListTasks;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Task;
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

    // Create required relationships for the tests
    $this->customer = Customer::factory()->recycle($this->user)->create();
    $this->supplier = Supplier::factory()->recycle($this->user)->create();
    $this->contract = Contract::factory()->recycle([$this->user, $this->customer, $this->supplier])->create();
});

it('can see tasks', function () {
    // Create tasks for the current user
    $tasks = Task::factory()
        ->count(5)
        ->active()
        ->recycle([$this->user, $this->contract])
        ->create();

    // Create tasks for another user
    $otherUser = User::factory()->create();
    $otherCustomer = Customer::factory()->recycle($otherUser)->create();
    $otherSupplier = Supplier::factory()->recycle($otherUser)->create();
    $otherContract = Contract::factory()
        ->recycle([$otherUser, $otherCustomer, $otherSupplier])
        ->create();

    $otherUserTasks = Task::factory()
        ->count(5)
        ->active()
        ->recycle([$otherUser, $otherContract])
        ->create();

    // Test that the user can see their own tasks but not others'
    livewire(ListTasks::class)
        ->assertCanSeeTableRecords($tasks) // can see current user records
        ->assertCanNotSeeTableRecords($otherUserTasks); // but can't see other user records
});

it('can create task', function () {
    // Prepare task data
    $taskData = [
        'contract_id' => $this->contract->id,
        'name' => 'Test Task',
        'url' => 'https://example.com',
        'note' => 'This is a test task',
        'active' => true,
    ];

    // Test creating a new task
    livewire(ListTasks::class)
        ->callAction(CreateAction::class, $taskData)
        ->assertHasNoActionErrors();

    // Verify the task was created in the database
    assertDatabaseHas('tasks', array_merge(
        $taskData,
        ['user_id' => $this->user->id]
    ));
});

it('can edit task', function () {
    // Create a task to edit
    $task = Task::factory()
        ->recycle([$this->user, $this->contract])
        ->active()
        ->create([
            'name' => 'Old Task',
            'url' => 'https://old-example.com',
            'note' => 'This is a old task',
        ]);
    $task->save();

    // New data for the task
    $newTaskData = [
        'name' => 'New Task',
        'url' => 'https://new-example.com',
        'note' => 'This is a new task',
        'active' => false,
    ];

    // Test editing the task
    livewire(ListTasks::class)
        ->callTableAction(name: EditAction::class, record: $task, data: $newTaskData)
        ->assertHasNoTableActionErrors();

    // Verify the task was updated in the database
    assertDatabaseHas('tasks', $newTaskData);
});

it('can filter active tasks', function () {
    // Create active and inactive tasks
    $activeTasks = Task::factory()
        ->count(3)
        ->recycle([$this->user, $this->contract])
        ->create(['active' => true]);

    $inactiveTasks = Task::factory()
        ->count(3)
        ->recycle([$this->user, $this->contract])
        ->create(['active' => false]);

    // Test filtering active tasks (default filter)
    livewire(ListTasks::class)
        ->assertCanSeeTableRecords($activeTasks)
        ->assertCanNotSeeTableRecords($inactiveTasks);

    // Test removing the filter to see all tasks
    livewire(ListTasks::class)
        ->removeTableFilter('active')
        ->assertCanSeeTableRecords($activeTasks)
        ->assertCanSeeTableRecords($inactiveTasks);
});

it('can bulk delete tasks', function () {
    // Create tasks for the current user
    $tasks = Task::factory()
        ->count(10)
        ->active()
        ->recycle([$this->user, $this->contract])
        ->create();

    // Create tasks for another user
    $otherUser = User::factory()->create();
    $otherCustomer = Customer::factory()->recycle($otherUser)->create();
    $otherSupplier = Supplier::factory()->recycle($otherUser)->create();
    $otherContract = Contract::factory()
        ->recycle([$otherUser, $otherCustomer, $otherSupplier])
        ->create();

    Task::factory()
        ->count(10)
        ->recycle([$otherUser, $otherContract])
        ->create();

    // Test bulk deleting tasks
    livewire(ListTasks::class)
        ->callTableBulkAction(DeleteBulkAction::class, $tasks);

    // Verify all selected tasks were deleted
    foreach ($tasks as $task) {
        $this->assertModelMissing($task);
    }

    // Verify other user tasks remain
    assertDatabaseCount('tasks', 10);
});

it('can bulk deactivate tasks', function () {
    // Create active tasks for the current user
    $tasks = Task::factory()
        ->count(5)
        ->recycle([$this->user, $this->contract])
        ->create(['active' => true]);

    // Test bulk deactivating tasks
    livewire(ListTasks::class)
        ->callTableBulkAction('deactivate', $tasks);

    // Verify all selected tasks were deactivated
    foreach ($tasks as $task) {
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'active' => false,
        ]);
    }
});
