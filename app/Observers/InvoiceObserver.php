<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Task;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        $this->updateContentAndAmount($invoice);
    }

    public function updated(Invoice $invoice): void
    {
        if ($invoice->isDirty(['contract_id', 'year', 'month'])) {
            $this->updateContentAndAmount($invoice);
        }
    }

    public function deleted(Invoice $invoice): void {}

    public function restored(Invoice $invoice): void {}

    public function forceDeleted(Invoice $invoice): void {}

    private function updateContentAndAmount(Invoice $invoice): void
    {
        // Gather tasks and their hours for invoice contract in specific year + month
        $content = Task::with(['taskHours' => fn ($query) => $query->whereYear('date', $invoice->year)->whereMonth('date', $invoice->month)])
            ->where('contract_id', $invoice->contract_id)
            ->orderBy('name')
            ->get()
            // filter only task with hours
            ->filter(fn (Task $task) => $task->taskHours->sum('hours'))
            // key by task id
            ->keyBy('id')
            // and transform array to desired output
            ->map(fn (Task $task) => [
                'name' => $task->name,
                'url' => $task->url,
                'hours' => round($task->taskHours->sum('hours'), 1),
                'amount' => round($task->taskHours->sum('hours') * $invoice->contract->price_per_hour, 2),
            ]);

        // Calculate amount from content
        $amount = $content->sum('amount');

        // Update invoice with content + total_amount
        $invoice->updateQuietly([
            'content' => $content,
            'total_amount' => $amount,
        ]);
    }
}
