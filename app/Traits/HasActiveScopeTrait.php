<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveScopeTrait
{
    public function scopeActive(Builder $builder): void
    {
        $builder->where('active', true);
    }
}
