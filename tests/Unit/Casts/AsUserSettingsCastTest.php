<?php

declare(strict_types=1);

use App\Casts\AsUserSettingsCast;
use App\Models\User;
use App\ValueObject\UserSettingsValueObject;
use Illuminate\Support\Facades\Log;

it('return correct default values', function () {
    expect(AsUserSettingsCast::getDefaults())
        ->toEqual(new UserSettingsValueObject(
            dueDateOffset: 14,
        ));
});

it('return default value when setting key not found', function () {
    Log::shouldReceive('error')
        ->once();

    expect(
        new AsUserSettingsCast()->get(
            model: new User(),
            key: 'settings',
            value: '{"foo": "bar"}',
            attributes: []
        )
    )
        ->toEqual(AsUserSettingsCast::getDefaults());
});

it('return correct value', function () {
    expect(
        new AsUserSettingsCast()->get(
            model: new User(),
            key: 'settings',
            value: '{"dueDateOffset": 9}',
            attributes: []
        )
    )
        ->toEqual(new UserSettingsValueObject(dueDateOffset: 9));
});
