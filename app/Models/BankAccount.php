<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\KeyValueOptionsContract;
use App\Traits\HasCurrentUserScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model implements KeyValueOptionsContract
{
    use HasCurrentUserScopeTrait, HasFactory;

    protected $guarded = [];

    /**
     * Key => value options for the bank account
     *
     * @return array<int, string>
     */
    public static function getOptions(): array
    {
        return BankAccount::query()
            ->currentUser()
            ->orderBy('bank_name')
            ->orderBy('account_number')
            ->get()
            ->keyBy('id')
            ->map(fn (BankAccount $account) => "{$account->account_number}/{$account->bank_code} - {$account->bank_name}")
            ->toArray();
    }
}
