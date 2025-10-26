<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\HomeWeightService;
use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class HomeWeightServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HomeWeightService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HomeWeightService();
    }

    // 週単位の体重データ取得テスト
    // 直近7日分の体重から日別・週平均が正しく返ることを確認
    public function test_get_weight_data_returns_correct_structure_for_week()
    {
        $user = User::factory()->create();
        $today = Carbon::today();

        // 7日分の体重データ作成
        foreach (range(0, 6) as $i) {
            Record::factory()->create([
                'user_id' => $user->id,
                'date' => $today->copy()->subDays($i),
                'weight' => 60 + $i,
            ]);
        }

        $data = $this->service->getWeightData($user);

        $this->assertArrayHasKey('week', $data);
        $this->assertCount(7, $data['week']['weights']);
        $this->assertNotNull($data['week']['average']);
    }

    // 月単位の体重データ取得テスト
    // 直近28日（4週間）の体重から週ごとの平均と月平均が返ることを確認
    public function test_get_weight_data_returns_correct_structure_for_month()
    {
        $user = User::factory()->create();
        $today = Carbon::today();

        // 28日分（4週間）の体重データ
        foreach (range(0, 27) as $i) {
            Record::factory()->create([
                'user_id' => $user->id,
                'date' => $today->copy()->subDays($i),
                'weight' => 60 + ($i % 5),
            ]);
        }

        $data = $this->service->getWeightData($user);

        $this->assertArrayHasKey('month', $data);
        $this->assertCount(4, $data['month']['weights']); // 4週間分
        $this->assertNotNull($data['month']['average']);
        $this->assertCount(4, $data['month']['labels']);
        $this->assertNotEmpty($data['month']['fullPeriodLabel']);
    }

    // 年単位の体重データ取得テスト
    // 過去12か月の体重から月ごとの平均と年平均が返ることを確認
    public function test_get_weight_data_returns_correct_structure_for_year()
    {
        $user = User::factory()->create();
        $today = Carbon::today();

        // 過去12か月のレコード作成（各月1日）
        foreach (range(0, 11) as $i) {
            $date = $today->copy()->subMonths($i)->startOfMonth();
            Record::factory()->create([
                'user_id' => $user->id,
                'date' => $date,
                'weight' => 65 + $i,
            ]);
        }

        $data = $this->service->getWeightData($user);

        $this->assertArrayHasKey('year', $data);
        $this->assertCount(12, $data['year']['weights']); // 12か月分
        $this->assertNotNull($data['year']['average']);
        $this->assertCount(12, $data['year']['labels']);
        $this->assertCount(12, $data['year']['days']);
    }
}
