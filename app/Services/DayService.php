<?php

namespace App\Services;

use App\Models\Day;
use App\Models\Entity;
use Illuminate\Support\Facades\Auth;

class DayService
{

    /**
     * エンティティに属する全ての記念日を取得
     *
     * @param Entity $entity
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByEntity(Entity $entity)
    {
        return $entity->days;
    }

    /**
     * 記念日を作成
     *
     * @param Entity $entity
     * @param array $data
     * @return Day
     */
    public function create(Entity $entity, array $data): Day
    {
        $day = new Day();
        $day->entity_id = $entity->id;
        $day->name = $data['name'];
        $day->desc = $data['desc'] ?? null;
        $day->anniv_at = $data['anniv_at'];
        $day->save();

        return $day;
    }

    /**
     * 記念日を取得
     *
     * @param Entity $entity
     * @param Day $day
     * @return Day
     */
    public function get(Entity $entity, Day $day): Day
    {
        return $day;
    }

    /**
     * 記念日を更新
     *
     * @param Entity $entity
     * @param Day $day
     * @param array $data
     * @return Day
     */
    public function update(Entity $entity, Day $day, array $data): Day
    {
        $day->name = $data['name'];
        $day->desc = $data['desc'] ?? null;
        $day->anniv_at = $data['anniv_at'];
        $day->save();

        return $day;
    }

    /**
     * 記念日を削除
     *
     * @param Entity $entity
     * @param Day $day
     * @return void
     */
    public function delete(Entity $entity, Day $day): void
    {
        $day->delete();
    }
}