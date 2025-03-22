<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportResource extends Resource
{
    use HasGetQueryForCurrentUser;
    use HasTranslatedBreadcrumbAndNavigation;

    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('contract_id')
                    ->label(trans('base.contract'))
                    ->relationship(
                        name: 'contract',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            $query->where('user_id', auth()->id())->orderBy('name');
                        }
                    )
                    ->columnSpanFull()
                    ->required()
                    ->rules([
                        fn(Forms\Get $get): Closure => function ($attribute, $value, Closure $fail) use ($get): void {
                            if (
                                Report::where('user_id', auth()->id())
                                    ->where('year', $get('year'))
                                    ->where('month', $get('month'))
                                    ->exists()
                            ) {
                                $fail(trans('base.validation.report_unique'));
                            }
                        },
                    ]),

                Forms\Components\TextInput::make('year')
                    ->label(trans('base.year'))
                    ->numeric()
                    ->required()
                    ->default(now()->year),

                Forms\Components\TextInput::make('month')
                    ->label(trans('base.month'))
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(12)
                    ->default(now()->month),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('contract.name')
                    ->label(trans('base.contract'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->label(trans('base.period'))
                    ->getStateUsing(function (Report $record) {
                        return "{$record->year}/{$record->month}";
                    })
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->orderBy('year', $direction)
                            ->orderBy('month', $direction);
                    })
                    ->numeric(),

                TextColumn::make('hours')
                    ->label(trans('base.hours'))
                    ->numeric(decimalPlaces: 1)
                    ->getStateUsing(function (Report $record) {
                        return floatval(collect($record->content)->sum(fn($item) => collect($item)->sum('hours')));
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\Action::make('pdf')
                    ->label(trans('base.pdf'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(Report $record): string => route('report.pdf', ['report' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
        ];
    }
}
