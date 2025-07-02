<?php

namespace App\Services;

use Carbon\Carbon;

class DateCalculationService
{
    /**
     * 記念日から今日までの日数を計算
     *
     * @param string|null $anniversaryDate Y-m-d 形式の日付
     * @return int|null 日数（null の場合は記念日が設定されていない）
     */
    public function calculateDiffDays(?string $anniversaryDate): ?int
    {
        if ($anniversaryDate === null) {
            return null;
        }

        $anniversaryDateTime = Carbon::createFromFormat('Y-m-d', $anniversaryDate);
        $anniversaryDateTime->setTime(0, 0, 0, 0);
        
        $now = Carbon::now();
        $now->setTime(0, 0, 0, 0);

        // 未来日の場合
        if ($anniversaryDateTime >= $now) {
            return (int)abs($anniversaryDateTime->diffInDays($now));
        }

        // 今日が記念日の場合（月日が同じ）
        if ($anniversaryDateTime->month === $now->month && $anniversaryDateTime->day === $now->day) {
            return 0;
        }

        // 過去日の場合、今年の記念日を計算
        $thisYearAnniversary = $anniversaryDateTime->copy()->setYear($now->year);
        
        if ($thisYearAnniversary > $now) {
            // 今年の記念日がまだ来ていない
            return (int)abs($thisYearAnniversary->diffInDays($now));
        }
        
        // 今年の記念日は既に過ぎているので、来年の記念日までの日数
        $nextYearAnniversary = $thisYearAnniversary->addYear();
        return (int)abs($nextYearAnniversary->diffInDays($now));
    }

}