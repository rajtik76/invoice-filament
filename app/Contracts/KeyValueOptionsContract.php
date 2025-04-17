<?php

declare(strict_types=1);

namespace App\Contracts;

interface KeyValueOptionsContract
{
    public static function getOptions(): array;
}
