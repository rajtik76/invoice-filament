<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Filament\Resources\ContractResource;
use App\Models\Contract;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class TaskForm
{
    public static function form(): array
    {
        return [
            Forms\Components\Grid::make()
                ->columns(1)
                ->schema([
                    Select::make('contract_id')
                        ->label(trans('label.contract'))
                        ->relationship(
                            name: 'contract',
                            titleAttribute: 'name',
                            modifyQueryUsing: function (Builder $query, ?Task $record): void {
                                $query->where('user_id', auth()->id())
                                    ->when(! $record, fn (Builder $query) => $query->where('active', true))
                                    ->orderBy('name');
                            }
                        )
                        ->createOptionModalHeading(trans('label.create_contract'))
                        ->createOptionForm(ContractForm::form())
                        ->createOptionUsing(function (array $data): void {
                            ContractResource::createRecordForCurrentUser($data);
                        })
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->default(function (?Task $record): int {
                            return key(Contract::where('user_id', auth()->id())
                                ->when(! $record, fn (Builder $query) => $query->where('active', true))
                                ->pluck('name', 'id')
                                ->all()
                            );
                        })
                        ->selectablePlaceholder(false)
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label(trans('label.task_name'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('url')
                        ->label('URL')
                        ->maxLength(255)
                        ->default(null)
                        ->rule('url'),

                    Forms\Components\Textarea::make('note')
                        ->label(trans('label.note'))
                        ->maxLength(255)
                        ->default(null),

                    Forms\Components\Toggle::make('active')
                        ->label(trans('label.active'))
                        ->required()
                        ->default(true),
                ]),
        ];
    }
}
