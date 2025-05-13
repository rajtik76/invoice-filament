<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\KeyValueOptionsContract;
use App\Enums\CountryEnum;
use App\Filament\Forms\AddressForm;
use App\Filament\Resources\AddressResource;
use App\Traits\HasCurrentUserScopeTrait;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model implements KeyValueOptionsContract
{
    use HasCurrentUserScopeTrait, HasFactory;

    protected $fillable = [
        'user_id',
        'street',
        'city',
        'zip',
        'country',
    ];

    protected $casts = [
        'country' => CountryEnum::class,
    ];

    /**
     * Get key => value address options for current user
     *
     * @return array<int, string>
     */
    public static function getOptions(): array
    {
        return Address::query()
            ->currentUser()
            ->orderBy('country')
            ->orderBy('city')
            ->orderBy('street')
            ->get()
            ->keyBy('id')
            ->map(fn (Address $address) => "{$address->street}, {$address->zip} {$address->city}, {$address->country->countryName()}")
            ->all();
    }

    /**
     * Get address select with a new option
     */
    public static function getSelectWithNewOption(): Select
    {
        return Select::make('address_id')
            ->label(trans('label.address'))
            ->relationship(
                name: 'address',
                modifyQueryUsing: function (Builder $query): void {
                    $query->where('user_id', auth()->id())
                        ->orderBy('country')
                        ->orderBy('city')
                        ->orderBy('street');
                }
            )
            ->getOptionLabelFromRecordUsing(fn (Address $record): string => "{$record->street}, {$record->zip} {$record->city}, {$record->country->countryName()}")
            ->createOptionModalHeading(trans('label.create_address'))
            ->createOptionForm(AddressForm::form())
            ->createOptionUsing(function (array $data): void {
                AddressResource::createAddressForCurrentUser($data);
            })
            ->createOptionAction(fn (Action $action) => $action->slideOver())
            ->searchable()
            ->preload()
            ->required();
    }

    /**
     * Retrieve the full address as a formatted string
     */
    public function getFullAddress(): string
    {
        return "{$this->street}, {$this->zip} {$this->city}, {$this->country->countryName()}";
    }
}
