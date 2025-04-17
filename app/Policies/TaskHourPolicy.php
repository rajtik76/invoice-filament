<?php
declare(strict_types=1);

namespace App\Policies;

use App\Enums\InvoiceStatusEnum;
use App\Models\TaskHour;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskHourPolicy
{
    use HandlesAuthorization;

    public function view(User $user, TaskHour $taskHour): bool
    {
        return $user->id === $taskHour->user_id;
    }

    public function update(User $user, TaskHour $taskHour): bool
    {
        return $user->id === $taskHour->user_id && (!$taskHour->invoice || $taskHour->invoice->status === InvoiceStatusEnum::Draft);
    }

    public function delete(User $user, TaskHour $taskHour): bool
    {
        return $this->update($user, $taskHour);
    }
}
