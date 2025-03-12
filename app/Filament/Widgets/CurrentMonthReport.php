<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;

/**
 * @property Form $form
 */
class CurrentMonthReport extends Widget implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string $view = 'filament.widgets.current-month-report';

    protected static ?int $sort = 1;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('contract')
                ->label(trans('base.contract'))
                ->options(Contract::where('active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                )
                ->required(),
        ])
            ->statePath('data');
    }

    public function create(): void
    {
        $this->redirectRoute('view.current-month-report', $this->data['contract']);
    }
}
