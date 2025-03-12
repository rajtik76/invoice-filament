<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Enumerable;
use RamonRietdijk\LivewireTables\Actions\Action;

trait HasActiveActions
{
    public function actions(): array
    {
        return [
            ...parent::actions(),
            Action::make(__('base.deactivate'), 'deactivate', function (Enumerable $models): void {
                foreach ($models as $model) {
                    $this->authorize('update', $model);
                    $model->update(['active' => false]);
                }
            }),
            Action::make(__('base.activate'), 'activate', function (Enumerable $models): void {
                foreach ($models as $model) {
                    $this->authorize('update', $model);
                    $model->update(['active' => true]);
                }
            }),
        ];
    }
}
