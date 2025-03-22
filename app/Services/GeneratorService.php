<?php

declare(strict_types=1);

namespace App\Services;

class GeneratorService
{
    public static function generateFileName(array $title, string $suffix = '.pdf'): string
    {
        return str()->slug(implode(' ', $title)) . $suffix;
    }
}
