<?php

namespace App\Policies;

use App\Models\Entity;
use App\Models\User;

class EntityPolicy
{
    /**
     * エンティティを閲覧できるか判定
     */
    public function view(User $user, Entity $entity): bool
    {
        return $user->id === $entity->user_id;
    }

    /**
     * エンティティを更新できるか判定
     */
    public function update(User $user, Entity $entity): bool
    {
        return $user->id === $entity->user_id;
    }

    /**
     * エンティティを削除できるか判定
     */
    public function delete(User $user, Entity $entity): bool
    {
        return $user->id === $entity->user_id;
    }

    /**
     * エンティティに記念日を作成できるか判定
     */
    public function createDay(User $user, Entity $entity): bool
    {
        return $user->id === $entity->user_id;
    }
}
