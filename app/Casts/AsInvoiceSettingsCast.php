<?php

declare(strict_types=1);

namespace App\Casts;

use App\Enums\LocaleEnum;
use App\Traits\HasFillObjectPropertyWithDataTrait;
use App\ValueObject\InvoiceSettingsValueObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use ReflectionException;
use Throwable;

class AsInvoiceSettingsCast implements CastsAttributes
{
    use HasFillObjectPropertyWithDataTrait;

    public function get(Model $model, string $key, mixed $value, array $attributes): InvoiceSettingsValueObject
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
        if (! $value instanceof InvoiceSettingsValueObject) {
            throw new InvalidArgumentException('Invalid contract settings value.');
        }

        return json_encode($value);
    }

    /**
     * Retrieves the default invoice settings.
     */
    public static function getDefaults(): InvoiceSettingsValueObject
    {
        return new InvoiceSettingsValueObject(
            reverseCharge: false,
            invoiceLocale: LocaleEnum::English
        );
    }

    /**
     * Populates the default settings with the provided data.
     *
     * @param  array<string, mixed>  $data  Data to populate into the defaults.
     * @return InvoiceSettingsValueObject The updated settings object.
     *
     * @throws ReflectionException
     */
    private function writeDataIntoDefaults(array $data): InvoiceSettingsValueObject
    {
        // Get the default settings
        $defaults = static::getDefaults();

        return self::fillObjectPropertyWithData($data, $defaults);
    }
}
