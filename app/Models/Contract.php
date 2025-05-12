<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AsContractSettingsCast;
use App\Contracts\KeyValueOptionsContract;
use App\Enums\CurrencyEnum;
use App\Traits\HasCurrentUserScopeTrait;
use App\ValueObject\ContractSettingsValueObject;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read User $user
 * @property-read Customer $customer
 * @property-read Supplier $supplier
 *
 * Casts
 * =====
 * @property ContractSettingsValueObject $settings
 */
class Contract extends Model implements KeyValueOptionsContract
{
    use HasCurrentUserScopeTrait, HasFactory;

    protected $guarded = [];

    protected $casts = [
        'signed_at' => 'date',
        'currency' => CurrencyEnum::class,
        'settings' => AsContractSettingsCast::class,
    ];

    /**
     * @return array<int, string>
     */
    public static function getOptions(): array
    {
        return Contract::query()
            ->currentUser()
            ->orderBy('name')
            ->get()
            ->keyBy('id')
            ->map(fn (Contract $contract) => $contract->name)
            ->toArray();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('active', true);
    }
}
