<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;

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
        $this->form->fill([
            'generatedInvoiceNumber' => auth()->user()->settings->generatedInvoiceNumber,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('generatedInvoiceNumber')
                    ->label(trans('label.generated_invoice_number'))
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: trans('tooltip.generated_invoice_number'))
                    ->maxWidth('xs')
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
        // Get a user model
        $user = auth()->user();

        // Get user settings
        $settings = $user->settings;
        $settings->generatedInvoiceNumber = $this->data['generatedInvoiceNumber'];

        // Update user settings
        $user->settings = $settings;
        $user->save();

        Notification::make()
            ->title(trans('notification.profile_settings_updated'))
            ->success()
            ->send();
    }
}
