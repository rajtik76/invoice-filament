<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\KeyValueOptionsContract;
use App\Traits\HasCurrentUserScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Address $address
 */
class Customer extends Model implements KeyValueOptionsContract
{
    use HasCurrentUserScopeTrait, HasFactory;

    protected $guarded = [];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * @return array<int, string>
     */
    public static function getOptions(): array
    {
        return Customer::query()
            ->currentUser()
            ->orderBy('name')
            ->get()
            ->keyBy('id')
            ->map(fn (Customer $customer) => $customer->name)
            ->toArray();
    }
}
