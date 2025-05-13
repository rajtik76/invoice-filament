<?php

declare(strict_types=1);

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Models\TaskHour;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property Invoice $ownerRecord
 */
class InvoiceHoursRelationManager extends RelationManager
{
    protected static string $relationship = 'invoiceHours';

    public function isReadOnly(): bool
    {
        return $this->ownerRecord->status === InvoiceStatusEnum::Issued;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('task_hour_id')
                    ->required()
                    ->options(fn () => TaskHour::with('task')
                        ->currentUser()
                        ->doesntHave('invoice')
                        ->whereHas('task', fn (Builder $query) => $query->where('contract_id', $this->ownerRecord->contract_id))
                        ->latest('date')
                        ->orderBy('task_id')
                        ->get()
                        ->map(fn (TaskHour $taskHour) => [
                            'id' => $taskHour->id,
                            'name' => implode(' | ', [
                                str($taskHour->task->name)->limit(20)->toString(),
                                $taskHour->date->format('d.m.Y'),
                                $taskHour->hours . ' hours',
                            ]),
                        ])
                        ->pluck('name', 'id')
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('task_hour_id')
            ->defaultSort('taskHour.date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('taskHour.task.name')
                    ->label(trans('label.task'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('taskHour.date')
                    ->label(trans('label.date'))
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('taskHour.hours')
                    ->label(trans('label.hours'))
                    ->numeric(decimalPlaces: 1),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
