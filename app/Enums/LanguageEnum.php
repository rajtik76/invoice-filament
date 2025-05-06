<?php
declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumTranslationsTrait;

enum LanguageEnum: string
{
    use HasEnumTranslationsTrait;

    case English = 'en';
    case Czech = 'cs';
}
