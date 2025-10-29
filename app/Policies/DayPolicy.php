<?php

namespace App\Policies;

use App\Models\Day;
use App\Models\User;

class DayPolicy
{
    /**
     * 記念日を閲覧できるか判定
     */
    public function view(User $user, Day $day): bool
    {
        return $user->id === $day->entity->user_id;
    }

    /**
     * 記念日を更新できるか判定
     */
    public function update(User $user, Day $day): bool
    {
        return $user->id === $day->entity->user_id;
    }

    /**
     * 記念日を削除できるか判定
     */
    public function delete(User $user, Day $day): bool
    {
        return $user->id === $day->entity->user_id;
    }
}
