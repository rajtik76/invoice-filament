<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait HasLoggedUserScopeTrait
 *
 * @method static Builder loggedUser() Apply scope for filtering by the logged-in user
 */
trait HasLoggedUserScopeTrait
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    protected function loggedUser(Builder $query): void
    {
        $query->where('user_id', auth()->id());
    }
}
