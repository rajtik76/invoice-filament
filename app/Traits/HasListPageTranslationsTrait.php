<?php

declare(strict_types=1);

namespace App\Traits;

use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;

/**
 * @mixin ListRecords
 */
trait HasListPageTranslationsTrait
{
    /**
     * Get the title of the resource.
     */
    public function getTitle(): string|Htmlable
    {
        /** @var class-string<resource> $resource */
        $resource = static::$resource;

        return $resource::getNavigationLabel();
    }
}
