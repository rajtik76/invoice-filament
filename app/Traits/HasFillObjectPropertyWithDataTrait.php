<?php

declare(strict_types=1);

namespace App\Traits;

use BackedEnum;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

trait HasFillObjectPropertyWithDataTrait
{
    /**
     * Populates public properties of a given object with data from the provided array.
     *
     * Iterates through the provided data and sets values to existing public properties
     * on the given object. If a property does not exist or is not public, a warning
     * is logged.
     *
     * @template T of object
     *
     * @param  T  $valueObject
     * @return T
     *
     * @throws ReflectionException If a class does not exist in runtime reflection.
     */
    private static function fillObjectPropertyWithData(array $data, object $valueObject): object
    {
        collect($data)->each(function ($value, $property) use ($valueObject): void {
            // Use ReflectionClass to check visibility
            $reflection = new ReflectionClass($valueObject);

            // Check if the property exists
            if ($reflection->hasProperty($property)) {
                // Get the property reflection
                $propertyReflection = $reflection->getProperty($property);

                // Check if the property is public
                if ($propertyReflection->isPublic()) {
                    // Get the property type
                    $propertyType = $propertyReflection->getType();

                    // Check if the property type is ReflectionNamedType and exists in enums
                    if ($propertyType instanceof ReflectionNamedType && enum_exists($propertyType->getName())) {
                        // Get enum class name
                        $enumClass = $propertyType->getName();

                        // Check if the enum class is backed enum
                        if (is_subclass_of($enumClass, BackedEnum::class)) {
                            // Set the property value as enum
                            $valueObject->$property = $enumClass::from($value);
                        } else {
                            throw new InvalidArgumentException("Enum class {$enumClass} isn't a subclass of BackedEnum.");
                        }
                    } else {
                        // Set the property value
                        $valueObject->$property = $value;
                    }
                }
            } else {
                Log::warning("Settings property {$property} does not exist.");
            }
        });

        return $valueObject;
    }
}
