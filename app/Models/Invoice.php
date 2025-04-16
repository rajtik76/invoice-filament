<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CurrencyEnum;
use App\Enums\InvoiceStatusEnum;
use App\Traits\HasLoggedUserScopeTrait;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Casts
 * =====
 * @property-read CarbonImmutable $issue_date
 * @property-read CarbonImmutable $due_date
 * @property-read CurrencyEnum $currency
 * @property-read InvoiceStatusEnum $status
 *
 * Relations
 * =========
 * @property-read Contract $contract
 */
class Invoice extends Model
{
    use HasFactory;
    use HasLoggedUserScopeTrait;

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'immutable_date',
        'due_date' => 'immutable_date',
        'currency' => CurrencyEnum::class,
        'status' => InvoiceStatusEnum::class,
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function invoiceHours(): HasMany
    {
        return $this->hasMany(InvoiceHour::class);
    }

    public function taskHours(): HasManyThrough
    {
        return $this->hasManyThrough(TaskHour::class, InvoiceHour::class, 'invoice_id', 'id', 'id', 'task_hour_id');
    }
}
