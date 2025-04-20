<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\InvoiceHour;
use App\Models\Task;
use App\Models\TaskHour;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create table invoice_hours
        Schema::create('invoice_hours', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Invoice::class)
                ->constrained('invoices')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignIdFor(TaskHour::class)
                ->constrained('task_hours')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->unique(['task_hour_id']);
        });

        // Fill table from task_hours based on invoices
        foreach (Invoice::all() as $invoice) {
            $taskHours = TaskHour::whereIntegerInRaw(column: 'task_id', values: Task::where('contract_id', $invoice->contract_id)->pluck('id'))
                ->whereYear('date', $invoice->year)
                ->whereMonth('date', $invoice->month)
                ->get()
                ->map(fn (TaskHour $taskHour): array => ['task_hour_id' => $taskHour->id, 'invoice_id' => $invoice->id])
                ->all();

            InvoiceHour::upsert($taskHours, ['task_hour_id']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_hours');
    }
};
