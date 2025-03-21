<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ReportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Contract $contract
 */
#[ObservedBy(ReportObserver::class)]
class Report extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'content' => 'json',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
