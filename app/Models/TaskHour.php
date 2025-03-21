<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCurrentUserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Task $task
 */
class TaskHour extends Model
{
    use HasCurrentUserScope, HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
