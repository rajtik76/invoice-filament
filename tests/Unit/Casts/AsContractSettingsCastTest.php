<?php

declare(strict_types=1);

use App\Casts\AsContractSettingsCast;
use App\Enums\LocaleEnum;
use App\Models\Contract;
use App\ValueObject\ContractSettingsValueObject;
use Illuminate\Support\Facades\Log;

it('return correct default values', function () {
    expect(AsContractSettingsCast::getDefaults())
        ->toEqual(new ContractSettingsValueObject(
            reverseCharge: false,
            invoiceLocale: LocaleEnum::English,
        ));
});

it('return default value when setting key not found', function () {
    Log::shouldReceive('error')
        ->once();

    expect(
        (new AsContractSettingsCast)->get(
            model: new Contract,
            key: 'settings',
            value: '{"foo": "bar"}',
            attributes: []
        )
    )
        ->toEqual(AsContractSettingsCast::getDefaults());
});

it('return correct value', function () {
    expect(
        (new AsContractSettingsCast)->get(
            model: new Contract,
            key: 'settings',
            value: '{"reverseCharge": true, "invoiceLocale": "cs"}',
            attributes: []
        )
    )
        ->toEqual(new ContractSettingsValueObject(reverseCharge: true, invoiceLocale: LocaleEnum::Czech));
});
