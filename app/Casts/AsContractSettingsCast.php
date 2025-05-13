<?php

declare(strict_types=1);

namespace App\Casts;

use App\Enums\LocaleEnum;
use App\Traits\HasFillObjectPropertyWithDataTrait;
use App\ValueObject\ContractSettingsValueObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use ReflectionException;
use Throwable;

class AsContractSettingsCast implements CastsAttributes
{
    use HasFillObjectPropertyWithDataTrait;

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        try {
            return $this->writeDataIntoDefaults(json_decode(json: $value, associative: true));
        } catch (Throwable $t) {
            Log::error($t->getMessage());

            return static::getDefaults();
        }
    }

    /**
     * Encodes the provided contract settings value object into a JSON string.
     *
     * @throws InvalidArgumentException If the provided value is not an instance of ContractSettingsValueObject.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value instanceof ContractSettingsValueObject) {
            throw new InvalidArgumentException('Invalid contract settings value.');
        }

        return json_encode($value);
    }

    /**
     * Retrieves the default user settings.
     */
    public static function getDefaults(): ContractSettingsValueObject
    {
        return new ContractSettingsValueObject(
            reverseCharge: false,
            invoiceLocale: LocaleEnum::English
        );
    }

    /**
     * Updates the default user settings with the provided data.
     *
     * @param  array<string, mixed>  $data  An associative array of property names and their values to update.
     * @return ContractSettingsValueObject Updated instance of the user settings with applied changes.
     *
     * @throws ReflectionException
     */
    private function writeDataIntoDefaults(array $data): ContractSettingsValueObject
    {
        // Get the default settings
        $defaults = static::getDefaults();

        return self::fillObjectPropertyWithData($data, $defaults);
    }
}
