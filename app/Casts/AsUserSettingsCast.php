<?php
declare(strict_types=1);

namespace App\Casts;

use App\ValueObject\UserSettingsValueObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Throwable;

class AsUserSettingsCast implements CastsAttributes
{
    public function get(
        Model  $model,
        string $key,
        mixed  $value,
        array  $attributes): UserSettingsValueObject
    {
        try {
            return $this->writeDataIntoDefaults(json_decode(json: $value, associative: true));
        } catch (Throwable $t) {
            Log::error($t->getMessage());
            return static::getDefaults();
        }
    }

    public function set(
        Model  $model,
        string $key,
        mixed  $value,
        array  $attributes): string
    {
        if (!$value instanceof UserSettingsValueObject) {
            throw new InvalidArgumentException('Invalid user settings value.');
        }

        return json_encode($value);
    }

    /**
     * Retrieves the default user settings.
     *
     * @return UserSettingsValueObject The default settings, including the predefined due date offset.
     */
    public static function getDefaults(): UserSettingsValueObject
    {
        return new UserSettingsValueObject(
            dueDateOffset: 14
        );
    }

    /**
     * Populates default user settings with provided data if properties are public.
     *
     * Iterates through the given data and updates the corresponding public properties
     * of the default user settings object, ensuring property existence and visibility.
     *
     * @param array{
     *     dueDateOffset?: int
     * } $data
     * @return UserSettingsValueObject The updated user settings object.
     * @throws ReflectionException
     */
    private function writeDataIntoDefaults(array $data): UserSettingsValueObject
    {
        // Get the default settings
        $userSettingsDefault = static::getDefaults();

        collect($data)->each(function ($value, $property) use ($userSettingsDefault): void {
            // Use ReflectionClass to check visibility
            $reflection = new ReflectionClass($userSettingsDefault);

            // Check if the property exists
            if ($reflection->hasProperty($property)) {
                // Get the property reflection
                $propertyReflection = $reflection->getProperty($property);

                // Check if the property is public
                if ($propertyReflection->isPublic()) {
                    // Set the property value
                    $userSettingsDefault->$property = $value;
                }
            } else {
                Log::warning("User settings property {$property} does not exist.");
            }
        });

        return $userSettingsDefault;
    }
}
