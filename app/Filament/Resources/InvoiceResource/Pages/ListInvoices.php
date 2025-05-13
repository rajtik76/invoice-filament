<?php

declare(strict_types=1);

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Enums\InvoiceStatusEnum;
use App\Enums\LocaleEnum;
use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceHour;
use App\Models\TaskHour;
use App\Traits\HasListPageTranslationsTrait;
use App\ValueObject\InvoiceSettingsValueObject;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;
use Throwable;

class ListInvoices extends ListRecords
{
    use HasListPageTranslationsTrait;

    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('label.create_invoice'))
                ->modalHeading(trans('label.create_invoice'))
                ->slideOver()
                ->mutateFormDataUsing(function (array $data) {
                    // Get settings
                    $settings = new InvoiceSettingsValueObject(
                        reverseCharge: data_get($data, 'settings.reverse_charge'),
                        invoiceLocale: LocaleEnum::from(data_get($data, 'settings.invoice_locale'))
                    );

                    // Set settings
                    $data['settings'] = $settings;

                    return $data;
                })
                ->using(function (array $data): Invoice {
                    try {
                        DB::beginTransaction();

                        $invoice = Invoice::create([
                            'user_id' => auth()->id(),
                            'contract_id' => $data['contract_id'],
                            'number' => $data['number'],
                            'status' => InvoiceStatusEnum::Draft,
                            'settings' => $data['settings'],
                        ]);

                        // Assign all unassigned hours from current month and same customer to invoice
                        if (data_get($data, 'prepare_hours')) {
                            $taskHourIds = TaskHour::query()->currentUser()
                                ->doesntHave('invoice')
                                ->whereHas('task', fn ($query) => $query->where('contract_id', $data['contract_id']))
                                ->whereYear('date', now()->year)
                                ->whereMonth('date', now()->month)
                                ->get()
                                ->map(fn (TaskHour $taskHour) => ['invoice_id' => $invoice->id, 'task_hour_id' => $taskHour->id])
                                ->all();

                            if ($taskHourIds) {
                                InvoiceHour::upsert($taskHourIds, ['task_hour_id']);
                            }
                        }

                        DB::commit();

                        return $invoice;
                    } catch (Throwable $t) {
                        DB::rollBack();

                        Notification::make()
                            ->title(trans('notification.invoice_create_failed'))
                            ->body($t->getMessage())
                            ->danger()
                            ->send();

                        throw new Halt;
                    }
                }),
        ];
    }
}
