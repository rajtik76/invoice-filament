<?php
declare(strict_types=1);

namespace App\DTO;

final readonly class TaskDTO
{
    public function __construct(
        public int     $contract_id,
        public int     $user_id,
        public string  $name,
        public ?string $url,
        public ?string $note,
        public bool    $active,
    )
    {
    }
}
