<?php

namespace Tests\Unit\Services;

use App\Services\DateCalculationService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class DateCalculationServiceTest extends TestCase
{
    private DateCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DateCalculationService();
    }

    /** @test */
    public function calculateDiffDays_returns_null_for_null_date()
    {
        $result = $this->service->calculateDiffDays(null);

        $this->assertNull($result);
    }

    /** @test */
    public function calculateDiffDays_returns_zero_for_today()
    {
        // 今日の日付を設定
        $today = Carbon::now()->format('Y-m-d');

        $result = $this->service->calculateDiffDays($today);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function calculateDiffDays_returns_correct_days_for_future_date()
    {
        // 10日後の日付を設定
        $futureDate = Carbon::now()->addDays(10)->format('Y-m-d');

        $result = $this->service->calculateDiffDays($futureDate);

        $this->assertEquals(10, $result);
    }

    /** @test */
    public function calculateDiffDays_returns_zero_for_anniversary_today()
    {
        // 過去の年だが、今日と同じ月日
        $anniversaryDate = Carbon::now()->subYear()->format('Y-m-d');

        $result = $this->service->calculateDiffDays($anniversaryDate);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function calculateDiffDays_returns_correct_days_for_this_year_anniversary()
    {
        // 今年の記念日が1か月後
        $thisYearAnniversary = Carbon::now()->addMonth()->format('Y-m-d');

        $result = $this->service->calculateDiffDays($thisYearAnniversary);

        // 約30日後（月によって異なるが、概算）
        $this->assertGreaterThan(25, $result);
        $this->assertLessThan(35, $result);
    }

    /** @test */
    public function calculateDiffDays_returns_correct_days_for_next_year_anniversary()
    {
        // 今年の記念日が既に過ぎている場合（1か月前）
        $pastAnniversary = Carbon::now()->subMonth()->format('Y-m-d');

        $result = $this->service->calculateDiffDays($pastAnniversary);

        // 来年の記念日まで約11か月（330日程度）
        $this->assertGreaterThan(300, $result);
        $this->assertLessThan(370, $result);
    }

    /** @test */
    public function calculateDiffDays_handles_leap_year_correctly()
    {
        // うるう年のテスト（2月29日）
        Carbon::setTestNow(Carbon::create(2024, 2, 28)); // 2024年はうるう年

        $leapDay = '2024-02-29';
        $result = $this->service->calculateDiffDays($leapDay);

        $this->assertEquals(1, $result);

        Carbon::setTestNow(); // テスト時刻をリセット
    }

    /** @test */
    public function calculateDiffDays_handles_year_boundary_correctly()
    {
        // 年末年始の境界テスト
        Carbon::setTestNow(Carbon::create(2023, 12, 31));

        $newYear = '2024-01-01';
        $result = $this->service->calculateDiffDays($newYear);

        $this->assertEquals(1, $result);

        Carbon::setTestNow(); // テスト時刻をリセット
    }

    /** @test */
    public function calculateDiffDays_handles_same_date_different_year()
    {
        // 同じ月日だが異なる年
        Carbon::setTestNow(Carbon::create(2023, 6, 15));

        // 過去の同じ日付
        $pastSameDate = '2020-06-15';
        $result = $this->service->calculateDiffDays($pastSameDate);

        $this->assertEquals(0, $result);

        Carbon::setTestNow(); // テスト時刻をリセット
    }
}
