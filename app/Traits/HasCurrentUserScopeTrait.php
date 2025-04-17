<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasCurrentUserScopeTrait
{
    #[Scope]
    protected function currentUser(Builder $builder): void
    {
        $builder->where('user_id', auth()->id());
    }
}
