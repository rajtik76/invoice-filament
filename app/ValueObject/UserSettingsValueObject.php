<?php

declare(strict_types=1);

namespace App\ValueObject;

use JsonSerializable;

final class UserSettingsValueObject implements JsonSerializable
{
    public function __construct(
        public bool $generatedInvoiceNumber,
    ) {}

    /**
     * @return array{
     *     generatedInvoiceNumber: bool
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'generatedInvoiceNumber' => $this->generatedInvoiceNumber,
        ];
    }
}
