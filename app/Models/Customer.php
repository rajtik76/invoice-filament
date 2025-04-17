<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\KeyValueOptionsContract;
use App\Traits\HasCurrentUserScopeTrait;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
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

    /**
     * Get form
     *
     * @return array<int, mixed>
     */
    public static function getForm(): array
    {
        return [
            Grid::make()
                ->columns(1)
                ->schema([
                    Address::getSelectWithNewOption(),

                    Split::make([
                        TextInput::make('name')
                            ->label(trans('base.customer'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('registration_number')
                            ->label(trans('base.registration_number'))
                            ->maxLength(255)
                            ->default(null),

                        TextInput::make('vat_number')
                            ->label(trans('base.vat'))
                            ->required()
                            ->maxLength(255),
                    ]),
                ]),
        ];
    }
}
