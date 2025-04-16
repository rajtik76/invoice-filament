<?php
declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DeleteBulkWithPolicyAction extends DeleteBulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'delete_with_policy';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function () {
            $result = $this->process(static fn (Collection $records) => $records->every(fn(Model $record): bool => auth()->user()->can('delete', $record)));

            if (! $result) {
                $this->failure();

                return;
            }

            $this->process(static fn (Collection $records) => $records->each(fn (Model $record) => $record->delete()));

            $this->success();
        });
    }

    public function getFailureNotificationTitle(): ?string
    {
        return trans('notification.invoice_bulk_delete_failed');
    }
}
