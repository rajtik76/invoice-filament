<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\KeyValueOptionsContract;
use App\Traits\HasActiveScopeTrait;
use App\Traits\HasCurrentUserScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read User $user
 */
class Task extends Model implements KeyValueOptionsContract
{
    use HasActiveScopeTrait, HasCurrentUserScopeTrait, HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taskHours(): HasMany
    {
        return $this->hasMany(TaskHour::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /** @return array<int, string> */
    public static function getOptions(): array
    {
        return TaskHour::query()
            ->currentUser()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
