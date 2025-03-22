<?php

declare(strict_types=1);

namespace App\Filament\Resources;

trait HasTranslatedBreadcrumbAndNavigation
{
    /**
     * Get navigation label
     */
    public static function getNavigationLabel(): string
    {
        return static::getBreadcrumbTranslation();
    }

    /**
     * Get breadcrumb
     */
    public static function getBreadcrumb(): string
    {
        return static::getBreadcrumbTranslation();
    }

    /**
     * Get navigation translation from current breadcrumb name converted to snake string
     */
    protected static function getBreadcrumbTranslation(): string
    {
        return trans('navigation.' . str(parent::getBreadcrumb())->snake());
    }
}
