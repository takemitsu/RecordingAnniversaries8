<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Day extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function Entity()
    {
        return $this->belongsTo('App\Models\Entity');
    }

    protected $appends = ['diff_days'];

    public function getDiffDaysAttribute()
    {
        return $this->diff();
    }

    /**
     * 今日から何日か
     * @return int
     */
    public function diff()
    {
        if ($this->anniv_at == null) {
            return null;
        }

        $dt = Carbon::createFromFormat('Y-m-d', $this->anniv_at);
        $dt->setTime(0, 0, 0, 0);
        $now = Carbon::now();
        $now->setTime(0, 0, 0, 0);



        // 未来日か
        if ($dt >= $now) {
            return (int)abs($dt->diffInDays($now));
        }

        // 同じ日付か
        if ($dt->month == $now->month && $dt->day == $now->day) {
            return 0;
        }

        // 過去日なら
        $dt->setYear($now->year);
        if ($dt > $now) {
            return (int)abs($dt->diffInDays($now));
        }
        // 今年はもう終わっていたら
        $dt->addYear();
        return (int)abs($dt->diffInDays($now));
    }
}
