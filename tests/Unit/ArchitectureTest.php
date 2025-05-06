<?php

arch('Debug tools')
    ->expect('App')
    ->not->toUse(['die', 'dd', 'dump', 'ray']);

arch('Strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('Traits to be suffixed with "Trait"')
    ->expect('App\Traits')
    ->toBeTrait()
    ->toHaveSuffix('Trait');

arch('Actions must be a final class + with "Action" suffix and must have handle method')
    ->expect('App\Actions')
    ->toBeClass()
    ->toHaveSuffix('Action')
    ->toHaveMethod('handle');

arch('DTO\'s to be suffixed with "DTO" and must be final readonly class')
    ->expect('App\DTO')
    ->toBeClass()
    ->toHaveSuffix('DTO')
    ->toBeFinal()
    ->toBeReadonly();

arch('Traits are not defined else than in the "Traits" namespace')
    ->expect('App')
    ->not->toBeTrait()
    ->ignoring('App\Traits');

arch('Contracts to be suffixed with "Contract"')
    ->expect('App\Contracts')
    ->toBeInterface()
    ->toHaveSuffix('Contract');

arch('Contracts are not defined else than in the "Contracts" namespace')
    ->expect('App')
    ->not->toBeInterface()
    ->ignoring('App\Contracts');
