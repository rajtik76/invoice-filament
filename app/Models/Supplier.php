<?php

declare(strict_types=1);

namespace App\Models;

use App\Filament\Resources\BankAccountResource;
use App\Traits\HasCurrentUserScopeTrait;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Get form
     *
     * @return array<int, mixed>
     */
    public static function getForm(): array
    {
        return [
            Forms\Components\Grid::make()
                ->columns(1)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(trans('label.name'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Split::make([
                        Forms\Components\TextInput::make('email')
                            ->label(trans('label.email'))
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label(trans('label.phone'))
                            ->tel()
                            ->required()
                            ->maxLength(255),
                    ]),

                    Forms\Components\Split::make([
                        Forms\Components\TextInput::make('registration_number')
                            ->label(trans('label.registration_number'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('vat_number')
                            ->label(trans('label.vat'))
                            ->required()
                            ->maxLength(255),
                    ]),

                    Forms\Components\Split::make([
                        Address::getSelectWithNewOption(),
                    ]),

                    Forms\Components\Split::make([
                        Select::make('bank_account_id')
                            ->label(trans('label.bank_account'))
                            ->relationship(
                                name: 'bankAccount',
                                modifyQueryUsing: function (Builder $query): void {
                                    $query->where('user_id', auth()->id())
                                        ->orderBy('bank_code')
                                        ->orderBy('bank_name');
                                }
                            )
                            ->getOptionLabelFromRecordUsing(fn (BankAccount $record): string => "{$record->bank_name}, {$record->account_number} / {$record->bank_code}")
                            ->createOptionModalHeading(trans('label.create_bank_account'))
                            ->createOptionForm(BankAccount::getForm())
                            ->createOptionUsing(function (array $data): void {
                                BankAccountResource::createRecordForCurrentUser($data);
                            })
                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                ]),
        ];
    }
}
