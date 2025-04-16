<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Task;
use App\Models\TaskHour;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::all() as $user) {
            // Get random contract
            $contractClosure = fn(): Contract => Contract::where('user_id', $user->id)->inRandomOrder()->first();
            $contract = $contractClosure();

            // Create 6 tasks per contract
            $contractTasks = Task::factory()
                ->count(6)
                ->recycle([$user, $contract])
                ->create();

            // Create 10 task hours per task
            foreach ($contractTasks as $task) {
                TaskHour::factory()
                    ->count(10)
                    ->recycle([$user, $task])
                    ->create();
            }

            // Create two invoice with draft status per user with random contract
            Invoice::factory()
                ->count(2)
                ->recycle([$user, $contract])
                ->draft()
                ->create();

            // Create two invoices with issued status per user with random contract
            $issuedInvoices = Invoice::factory()
                ->count(2)
                ->recycle([$user, $contractClosure()])
                ->issued()
                ->create();

            // Assign task hours to issued invoices
            foreach ($issuedInvoices as $index => $invoice) {
                // Calculate which tasks to assign to this invoice (3 tasks per invoice)
                // Using modulo to ensure we cycle through the tasks properly
                $startIndex = ($index * 3) % $contractTasks->count();

                // Get 3 consecutive tasks for this invoice
                $tasksForInvoice = $contractTasks
                    ->slice($startIndex, 3)
                    ->when($startIndex + 3 > $contractTasks->count(), function ($collection) use ($contractTasks) {
                        // If we need to wrap around to the beginning of the collection
                        $remaining = 3 - $collection->count();
                        return $collection->concat($contractTasks->slice(0, $remaining));
                    });

                // For each assigned task, get some of its hours and attach to the invoice
                foreach ($tasksForInvoice as $task) {
                    // Get some hours that haven't been assigned to any invoice yet
                    $hoursToAssign = $task->taskHours()
                        ->whereDoesntHave('invoice')
                        ->limit(3)  // Assign 3 hours per task
                        ->get();

                    // Attach these hours to the current invoice
                    foreach ($hoursToAssign as $hour) {
                        $invoice->invoiceHours()->create([
                            'task_hour_id' => $hour->id
                        ]);
                    }
                }
            }

        }
    }
}
