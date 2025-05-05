<?php
declare(strict_types=1);

namespace App\ValueObject;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

final class UserSettingsValueObject implements JsonSerializable
{
    /**
     * @param int $dueDateOffset The number of days added to the invoice issue date to calculate the due date.
     */
    public function __construct(
        public int $dueDateOffset
    )
    {
    }

    /**
     * @return array{
     *     dueDateOffset: int
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'dueDateOffset' => $this->dueDateOffset,
        ];
    }
}
