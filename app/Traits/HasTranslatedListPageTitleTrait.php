<?php

declare(strict_types=1);

namespace App\Traits;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

/**
 * @mixin ListRecords
 */
trait HasTranslatedListPageTitleTrait
{
    public function getTitle(): string|Htmlable
    {
        return self::getBreadcrumbTranslation();
    }

    /**
     * Get navigation translation from current breadcrumb name converted to snake string
     */
    protected static function getBreadcrumbTranslation(): string
    {
        return static::$resource::getBreadcrumb();
    }
}
