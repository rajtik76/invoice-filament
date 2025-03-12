<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCurrentUserScope
{
    public function scopeCurrentUser(Builder $builder): void
    {
        $builder->where('user_id', auth()->id());
    }
}
