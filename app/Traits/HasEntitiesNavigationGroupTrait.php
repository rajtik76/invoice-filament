<?php

declare(strict_types=1);

namespace App\Traits;

use Filament\Resources\Resource;

/**
 * @mixin Resource
 */
trait HasEntitiesNavigationGroupTrait
{
    public static function getNavigationGroup(): ?string
    {
        return trans('base.entities');
    }
}
