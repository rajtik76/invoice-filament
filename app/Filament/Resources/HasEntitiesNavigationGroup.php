<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Resources\Resource;

/**
 * @mixin Resource
 */
trait HasEntitiesNavigationGroup
{
    public static function getNavigationGroup(): ?string
    {
        return trans('base.entities');
    }
}
