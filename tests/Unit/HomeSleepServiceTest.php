<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\HomeSleepService;
use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class HomeSleepServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HomeSleepService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HomeSleepService();
    }

    // 週単位の睡眠データ取得テスト
    // 直近7日分の睡眠時間から日別・週平均が正しく返ることを確認
    public function test_get_sleep_data_returns_correct_structure_for_week()
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $today = Carbon::today();

        // 7日分の睡眠データ作成
        foreach (range(0, 6) as $i) {
            Record::factory()->create([
                'user_id' => $userId,
                'date' => $today->copy()->subDays($i),
                'sleep_hours' => 6 + $i,
                'sleep_minutes' => 30,
            ]);
        }

        $data = $this->service->getSleepData($user);

        $this->assertArrayHasKey('week', $data);
        $this->assertCount(7, $data['week']['weekSleep']);
        $this->assertNotNull($data['week']['weekAverage']);
    }

    // 月単位の睡眠データ取得テスト
    // 直近28日（4週間）の睡眠時間から週ごとの平均と月平均が返ることを確認
    public function test_get_sleep_data_returns_correct_structure_for_month()
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $today = Carbon::today();

        // 28日分（4週間）のテストデータ
        foreach (range(0, 27) as $i) {
            Record::factory()->create([
                'user_id' => $userId,
                'date' => $today->copy()->subDays($i),
                'sleep_hours' => 6 + ($i % 3),
                'sleep_minutes' => 15,
            ]);
        }

        $data = $this->service->getSleepData($user);

        $this->assertArrayHasKey('month', $data);
        $this->assertCount(4, $data['month']['monthSleep']); // 週平均4件
        $this->assertNotNull($data['month']['monthAverage']);
        $this->assertCount(4, $data['month']['monthLabels']);
        $this->assertCount(4, $data['month']['monthDaysSleep']);
        $this->assertNotEmpty($data['month']['fullPeriodLabel']);
    }

    // 年単位の睡眠データ取得テスト
    // 過去12か月の睡眠時間から月ごとの平均と年平均が返ることを確認
    public function test_get_sleep_data_returns_correct_structure_for_year()
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $today = Carbon::today();

        // 過去12か月のレコード作成（各月1日）
        foreach (range(0, 11) as $i) {
            $date = $today->copy()->subMonths($i)->startOfMonth();
            Record::factory()->create([
                'user_id' => $userId,
                'date' => $date,
                'sleep_hours' => 7 + ($i % 2),
                'sleep_minutes' => 0,
            ]);
        }

        $data = $this->service->getSleepData($user);

        $this->assertArrayHasKey('year', $data);
        $this->assertCount(12, $data['year']['yearSleep']); // 月平均12件
        $this->assertNotNull($data['year']['yearAverage']);
        $this->assertCount(12, $data['year']['yearLabels']);
        $this->assertCount(12, $data['year']['yearDays']);
    }
}
