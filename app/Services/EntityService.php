<?php

namespace App\Services;

use App\Models\Entity;
use Illuminate\Support\Arr;
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

        // 記念日を差分日数でソート
        foreach ($entities as $key => $entity) {
            if (count($entity->days) == 0) {
                unset($entities[$key]);
            } else {
                // diff_days で sort
                $sorted = array_values(Arr::sort($entity->days, function ($value) {
                    return $value['diff_days'];
                }));
                unset($entities[$key]->days);
                $entities[$key]->days = $sorted;
            }
        }

        return $entities;
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
     *
     * @param array $data
     * @return Entity
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
     *
     * @param Entity $entity
     * @return Entity
     */
    public function getWithDays(Entity $entity): Entity
    {
        return $entity->load('days');
    }

    /**
     * エンティティを更新
     *
     * @param Entity $entity
     * @param array $data
     * @return Entity
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
     *
     * @param Entity $entity
     * @return void
     */
    public function delete(Entity $entity): void
    {
        $entity->delete();
    }
}