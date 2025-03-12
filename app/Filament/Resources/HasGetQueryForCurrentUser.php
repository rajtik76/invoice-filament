<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Builder;

trait HasGetQueryForCurrentUser
{
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }
}
