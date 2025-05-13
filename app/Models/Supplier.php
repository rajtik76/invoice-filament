<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCurrentUserScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Address $address
 * @property-read BankAccount $bankAccount
 */
class Supplier extends Model
{
    use HasCurrentUserScopeTrait, HasFactory;

    protected $guarded = [];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * @return array<int, string>
     */
    public static function getOptions(): array
    {
        return Supplier::query()
            ->currentUser()
            ->orderBy('name')
            ->get()
            ->keyBy('id')
            ->map(fn (Supplier $supplier) => $supplier->name)
            ->toArray();
    }
}
