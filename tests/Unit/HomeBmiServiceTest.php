<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\HomeBmiService;
use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class HomeBmiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HomeBmiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HomeBmiService();
    }

    // 週単位のBMIデータ取得テスト
    // 直近7日分の体重からBMIを計算し正しい構造で返ることを確認
    public function test_get_bmi_data_returns_correct_structure_for_week()
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $height = 1.7;
        $today = Carbon::today();

        foreach (range(0, 6) as $i) {
            Record::factory()->create([
                'user_id' => $userId,
                'date' => $today->copy()->subDays($i),
                'weight' => 60 + $i,
            ]);
        }

        $data = $this->service->getBmiData($userId, $height);

        $this->assertArrayHasKey('week', $data);
        $this->assertCount(7, $data['week']['bmis']);
        $this->assertNotNull($data['week']['average']);
    }

    // 月単位のBMIデータ取得テスト
    // 直近28日（4週間）の体重から週ごとの平均BMIが返ることを確認
    public function test_get_bmi_data_returns_correct_structure_for_month()
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $height = 1.7;
        $today = Carbon::today();

        foreach (range(0, 27) as $i) {
            Record::factory()->create([
                'user_id' => $userId,
                'date' => $today->copy()->subDays($i),
                'weight' => 60 + $i % 5,
            ]);
        }

        $data = $this->service->getBmiData($userId, $height);

        $this->assertArrayHasKey('month', $data);
        $this->assertCount(4, $data['month']['bmis']);
        $this->assertNotNull($data['month']['average']);
        $this->assertCount(4, $data['month']['labels']);
        $this->assertCount(4, $data['month']['days']);
        $this->assertNotEmpty($data['month']['fullPeriodLabel']);
    }

    // 年単位のBMIデータ取得テスト
    // 過去12か月の体重から月ごとの平均BMIが返ることを確認
    public function test_get_bmi_data_returns_correct_structure_for_year()
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $height = 1.7;
        $today = Carbon::today();

        foreach (range(0, 11) as $i) {
            $date = $today->copy()->subMonths($i)->startOfMonth();
            Record::factory()->create([
                'user_id' => $userId,
                'date' => $date,
                'weight' => 65 + $i,
            ]);
        }

        $data = $this->service->getBmiData($userId, $height);

        $this->assertArrayHasKey('year', $data);
        $this->assertCount(12, $data['year']['bmis']);
        $this->assertNotNull($data['year']['average']);
        $this->assertCount(12, $data['year']['labels']);
        $this->assertCount(12, $data['year']['days']);
    }
}
