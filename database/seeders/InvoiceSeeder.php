<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoiceHour;
use App\Models\Task;
use App\Models\TaskHour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    private const int LongInvoiceCount = 15;

    public function run(): void
    {
        foreach (User::all() as $user) {
            // Get a random contract
            $userContractsInRandomOrder = Contract::where('user_id', $user->id)->inRandomOrder()->get();
            $contract = $userContractsInRandomOrder->first();

            /*
             * LONG INVOICE START
             */

            // Create one invoice with 30 tasks to have a long invoice
            $longInvoiceTasks = Task::factory()
                ->count(self::LongInvoiceCount)
                ->recycle([$user, $contract])
                ->create();

            // Create one task hour per a long invoice task
            foreach ($longInvoiceTasks as $longInvoiceTask) {
                TaskHour::factory()
                    ->recycle([$user, $longInvoiceTask])
                    ->create();
            }

            // Create a long invoice
            $longInvoice = Invoice::factory()
                ->recycle([$user, $contract])
                ->issued()
                ->create();

            // Assign task hours to the long invoice
            InvoiceHour::factory()
                ->count(self::LongInvoiceCount)
                ->recycle($longInvoice)
                ->state(new Sequence(
                    ...TaskHour::whereIntegerInRaw('task_id', $longInvoiceTasks->pluck('id'))
                        ->get()
                        ->map(fn (TaskHour $item) => ['task_hour_id' => $item->id])
                        ->toArray())
                )
                ->create();

            /*
             * LONG INVOICE END
             */

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

            // Create two invoices with draft status per user with a random contract
            Invoice::factory()
                ->count(2)
                ->recycle([$user, $contract])
                ->draft()
                ->create();

            // Create two invoices with an issued status per user with a random contract
            $issuedInvoices = Invoice::factory()
                ->count(2)
                ->recycle([$user, $userContractsInRandomOrder->last()])
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
                            'task_hour_id' => $hour->id,
                        ]);
                    }
                }
            }

        }
    }
}
