<?php

declare(strict_types=1);

use App\Actions\CreateTaskAction;
use App\DTO\TaskDTO;
use App\Events\TaskCreatedEvent;
use App\Models\Contract;
use Illuminate\Support\Facades\Event;

it('Create task and dispatch event', function () {
    // Arrange
    Event::fake();

    $contract = Contract::factory()->create(['id' => 999]);
    $dto = new TaskDTO(
        contract_id: 999,
        user_id: $contract->user_id,
        name: 'Test',
        url: 'https://example.com',
        note: 'Test note',
        active: true,
    );

    // Act
    $task = new CreateTaskAction()->handle($dto);

    // Assert
    expect($task)
        ->contract_id->toBe(999)
        ->name->toBe('Test')
        ->url->toBe('https://example.com')
        ->note->toBe('Test note')
        ->active->toBeTrue();

    Event::assertDispatched(TaskCreatedEvent::class, function (TaskCreatedEvent $event) use ($task) {
        expect($event->task)->toBe($task);
        return true;
    });
});
