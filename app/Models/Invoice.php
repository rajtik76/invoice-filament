<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CurrencyEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'content' => 'json',
        'currency' => CurrencyEnum::class,
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get Filament form data
     *
     * @return array<int, Field>
     */
    public static function getForm(): array
    {
        return [
            Select::make('contract_id')
                ->options(Contract::where('user_id', auth()->id())->pluck('name', 'id'))
                ->nullable(false),
            Grid::make()
                ->columns(3)
                ->schema([
                    TextInput::make('number')
                        ->numeric()
                        ->required(),
                    TextInput::make('year')
                        ->numeric()
                        ->required()
                        ->minValue(now()->year)
                        ->default(now()->year),
                    TextInput::make('month')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(12)
                        ->default(now()->month),
                ]),
            Grid::make()
                ->schema([
                    DatePicker::make('issue_date')
                        ->required()
                        ->default(now()),
                    DatePicker::make('due_date')
                        ->required()
                        ->default(now()->addDays(7)),
                ]),
        ];
    }
}
