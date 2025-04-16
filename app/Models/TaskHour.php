<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCurrentUserScope;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property-read User $user
 * @property-read Task $task
 * @property-read Invoice|null $invoice
 */
class TaskHour extends Model
{
    use HasCurrentUserScope, HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function invoice(): HasOneThrough
    {
        return $this->hasOneThrough(related: Invoice::class, through: InvoiceHour::class, firstKey: 'task_hour_id', secondKey: 'id', localKey: 'id', secondLocalKey: 'invoice_id');
    }

    #[Scope]
    protected function contract(Builder $query, int $contract_id): void
    {
        $query->whereHas('task', fn (Builder $query) => $query->where('contract_id', $contract_id));
    }
}
