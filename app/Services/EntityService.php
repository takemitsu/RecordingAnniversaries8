<?php

namespace App\Services;

use App\Models\Entity;
use Illuminate\Support\Facades\Auth;

class EntityService
{
    /**
     * ダッシュボード用の記念日を含むエンティティを取得
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEntitiesForPickup()
    {
        $entities = Entity::where('user_id', Auth::id())
            ->has('days')
            ->with('days')
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->sortDaysByDiffDays($entities);
    }

    /**
     * エンティティの記念日を差分日数でソート
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $entities
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function sortDaysByDiffDays($entities)
    {
        return $entities->map(function ($entity) {
            if ($entity->days->isEmpty()) {
                return null;
            }

            // 記念日を diff_days でソート
            $entity->days = $entity->days->sortBy('diff_days')->values();

            return $entity;
        })->filter(); // null を除去
    }

    /**
     * ユーザーの全エンティティを取得
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllForUser()
    {
        return Entity::where('user_id', Auth::id())
            ->with('days')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * エンティティを作成
     */
    public function create(array $data): Entity
    {
        $entity = new Entity();
        $entity->user_id = Auth::id();
        $entity->name = $data['name'];
        $entity->desc = $data['desc'] ?? null;
        $entity->save();

        return $entity;
    }

    /**
     * エンティティを記念日と共に取得
     */
    public function getWithDays(Entity $entity): Entity
    {
        return $entity->load('days');
    }

    /**
     * エンティティを更新
     */
    public function update(Entity $entity, array $data): Entity
    {
        $entity->name = $data['name'];
        $entity->desc = $data['desc'] ?? null;
        $entity->status = $data['status'] ?? true;
        $entity->save();

        return $entity;
    }

    /**
     * エンティティを削除
     */
    public function delete(Entity $entity): void
    {
        $entity->delete();
    }
}
