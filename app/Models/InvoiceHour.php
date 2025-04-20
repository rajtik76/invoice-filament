<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Invoice $invoice
 * @property-read TaskHour $taskHour
 */
class InvoiceHour extends Model
{
    use HasFactory;

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function taskHour(): BelongsTo
    {
        return $this->belongsTo(TaskHour::class);
    }
}
