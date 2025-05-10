<?php

declare(strict_types=1);

namespace App\Traits;

trait HasResourceTranslationsTrait
{
    /**
     * Get navigation label
     */
    public static function getNavigationLabel(): string
    {
        return static::getNavigationTranslation();
    }

    /**
     * Get breadcrumb
     */
    public static function getBreadcrumb(): string
    {
        return static::getNavigationTranslation();
    }

    /**
     * Get navigation translation from the current breadcrumb name converted to snake string
     */
    protected static function getNavigationTranslation(): string
    {
        return trans('navigation.' . str(parent::getBreadcrumb())->snake());
    }
}
