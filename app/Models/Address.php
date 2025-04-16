<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\KeyValueOptions;
use App\Enums\CountryEnum;
use App\Filament\Resources\AddressResource;
use App\Traits\HasCurrentUserScope;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model implements KeyValueOptions
{
    use HasCurrentUserScope, HasFactory;

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
        return self::currentUser()
            ->orderBy('country')
            ->orderBy('city')
            ->orderBy('street')
            ->get()
            ->keyBy('id')
            ->map(fn (Address $address) => "{$address->street}, {$address->zip} {$address->city}, {$address->country->countryName()}")
            ->all();
    }

    public static function getForm(): array
    {
        return [
            Grid::make()
                ->columns(1)
                ->schema([
                    TextInput::make('street')
                        ->label(trans('base.street'))
                        ->required()
                        ->maxLength(255),
                ]),
            Grid::make()
                ->columns(3)
                ->schema([
                    TextInput::make('city')
                        ->label(trans('base.city'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('zip')
                        ->label(trans('base.zip'))
                        ->required()
                        ->maxLength(255),
                    Select::make('country')
                        ->label(trans('base.country'))
                        ->required()
                        ->options(CountryEnum::options()),
                ]),
        ];
    }

    /**
     * Get address select with new option
     */
    public static function getSelectWithNewOption(): Select
    {
        return Select::make('address_id')
            ->label(trans('base.address'))
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
            ->createOptionModalHeading(trans('base.create_address'))
            ->createOptionForm(Address::getForm())
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
