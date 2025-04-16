<?php
declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumTranslationsTrait;

enum InvoiceStatusEnum: string
{
    use HasEnumTranslationsTrait;

    case Draft = 'draft';
    case Issued = 'issued';
}
