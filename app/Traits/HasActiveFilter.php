<?php

declare(strict_types=1);

namespace App\Traits;

use RamonRietdijk\LivewireTables\Filters\BaseFilter;
use RamonRietdijk\LivewireTables\Filters\BooleanFilter;

trait HasActiveFilter
{
    /** @return BaseFilter[] */
    protected function filters(): array
    {
        return [
            ...parent::filters(),
            BooleanFilter::make(__('base.active'), 'active'),
        ];
    }
}
