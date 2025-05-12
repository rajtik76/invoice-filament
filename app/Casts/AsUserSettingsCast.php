<?php

declare(strict_types=1);

namespace App\Casts;

use App\Enums\LocaleEnum;
use App\Traits\HasFillObjectPropertyWithDataTrait;
use App\ValueObject\UserSettingsValueObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use ReflectionException;
use Throwable;

class AsUserSettingsCast implements CastsAttributes
{
    use HasFillObjectPropertyWithDataTrait;

    /**
     * Retrieves a user settings value object by decoding the provided attribute value
     * and merging it with default settings.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): UserSettingsValueObject
    {
        try {
            return $this->writeDataIntoDefaults(json_decode(json: $value, associative: true));
        } catch (Throwable $t) {
            Log::error($t->getMessage());

            return static::getDefaults();
        }
    }

    /**
     * Encodes the provided user settings value object into a JSON string.
     *
     * @throws InvalidArgumentException If the provided value is not a valid UserSettingsValueObject.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (! $value instanceof UserSettingsValueObject) {
            throw new InvalidArgumentException('Invalid user settings value.');
        }

        return json_encode($value);
    }

    /**
     * Retrieves the default user settings.
     */
    public static function getDefaults(): UserSettingsValueObject
    {
        return new UserSettingsValueObject(
            generatedInvoiceNumber: true,
        );
    }

    /**
     * Populates default user settings with provided data if properties are public.
     *
     * Iterates through the given data and updates the corresponding public properties
     * of the default user settings object, ensuring property existence and visibility.
     *
     * @param array{
     *     locale: LocaleEnum,
     *     generatedInvoiceNumber: bool
     * } $data
     * @return UserSettingsValueObject The updated user settings object.
     *
     * @throws ReflectionException
     */
    private function writeDataIntoDefaults(array $data): UserSettingsValueObject
    {
        // Get the default settings
        $userSettingsDefault = static::getDefaults();

        return self::fillObjectPropertyWithData($data, $userSettingsDefault);
    }
}
