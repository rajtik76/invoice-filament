<?php
declare(strict_types=1);

namespace App\Traits;

/**
 * @method cases()
 */
trait HasEnumTranslationsTrait
{
    /**
     * Retrieves translated cases for an enum.
     */
    public static function translatedCases(): array
    {
        // Return translated enum case
        return collect(self::cases())
            ->map(function ($case): string {
                return __(static::getBaseNameTranslationKey() . $case->value);
            })
            ->all();
    }

    /**
     * Get the translation for the current value based on the base name translation key.
     */
    public function translation(): string
    {
        return __(static::getBaseNameTranslationKey() . $this->value);
    }

    /**
     * Get the base name translation key for the enum.
     *
     * This method retrieves the short class name without the namespace,
     * removes the "Enum" suffix if it exists, converts the remaining name
     * to snake_case, and constructs a translation key string for the enum.
     *
     * @return string The translation key in the format "enum.{base_name}." where
     *                {base_name} is the name of the enum class in snake_case,
     *                with the "Enum" suffix removed if present.
     */
    protected static function getBaseNameTranslationKey(): string
    {
        // Get the short class name without namespace
        $className = class_basename(static::class);

        // Remove 'Enum' suffix if present and convert to snake_case
        $baseName = str($className)->replace('Enum', '')->snake()->toString();

        return "enum.{$baseName}.";
    }
}
