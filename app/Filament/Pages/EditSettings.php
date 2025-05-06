<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;

/**
 * @property Form $form
 */
class EditSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.edit-settings';

    protected static bool $shouldRegisterNavigation = false; // hide from the navigation bar

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Submit')
                ->submit('submit'),

            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url(Dashboard::getUrl()), // or another route
        ];
    }

    public function create(): void
    {
        // ...
    }
}
