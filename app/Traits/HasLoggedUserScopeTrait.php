<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasLoggedUserScopeTrait
 *
 * @method static Builder loggedUser() Apply scope for filtering by the logged-in user
 */
trait HasLoggedUserScopeTrait
{
    #[Scope]
    protected function loggedUser(Builder $query): void
    {
        $query->where('user_id', auth()->id());
    }
}
