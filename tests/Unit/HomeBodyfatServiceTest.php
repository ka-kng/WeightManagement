<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\HomeBodyfatService;
use App\Models\Record;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class HomeBodyfatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HomeBodyfatService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HomeBodyfatService();
    }

    // 週単位の体脂肪率データ取得テスト
    // 直近7日分の体重から体脂肪率を計算し正しい構造で返ることを確認
    public function test_get_bodyfat_data_returns_correct_structure_for_week()
    {
        $user = User::factory()->create([
            'birth_date' => now()->subYears(30), // 年齢30歳
            'height' => 1.7,
            'gender' => 1, // 男性
        ]);
        $userId = $user->id;
        $today = Carbon::today();

        // 7日分の体重データ作成
        foreach (range(0, 6) as $i) {
            Record::factory()->create([
                'user_id' => $userId,
                'date' => $today->copy()->subDays($i),
                'weight' => 60 + $i,
            ]);
        }

        $data = $this->service->getBodyFatData($user);

        $this->assertArrayHasKey('week', $data);
        $this->assertCount(7, $data['week']['weekBodyFat']);
        $this->assertNotNull($data['week']['weekAverage']);
    }

    // 月単位の体脂肪率データ取得テスト
    // 直近28日（4週間）の体重から週ごとの平均体脂肪率が返ることを確認
    public function test_get_bodyfat_data_returns_correct_structure_for_month()
    {
        $user = User::factory()->create([
            'birth_date' => now()->subYears(30),
            'height' => 1.7,
            'gender' => 1,
        ]);
        $userId = $user->id;
        $today = Carbon::today();

        // 28日分（4週間）のテストデータ
        foreach (range(0, 27) as $i) {
            Record::factory()->create([
                'user_id' => $userId,
                'date' => $today->copy()->subDays($i),
                'weight' => 60 + $i % 5,
            ]);
        }

        $data = $this->service->getBodyFatData($user);

        $this->assertArrayHasKey('month', $data);
        $this->assertCount(4, $data['month']['monthBodyFat']); // 週平均4件
        $this->assertNotNull($data['month']['monthAverage']);
        $this->assertCount(4, $data['month']['monthLabels']);
        $this->assertCount(4, $data['month']['monthDaysBodyFat']);
        $this->assertNotEmpty($data['month']['fullPeriodLabel']);
    }

    // 年単位の体脂肪率データ取得テスト
    // 過去12か月の体重から月ごとの平均体脂肪率が返ることを確認
    public function test_get_bodyfat_data_returns_correct_structure_for_year()
    {
        $user = User::factory()->create([
            'birth_date' => now()->subYears(30),
            'height' => 1.7,
            'gender' => 1,
        ]);
        $userId = $user->id;
        $today = Carbon::today();

        // 過去12か月のレコード作成
        foreach (range(0, 11) as $i) {
            $date = $today->copy()->subMonths($i)->startOfMonth();
            Record::factory()->create([
                'user_id' => $userId,
                'date' => $date,
                'weight' => 65 + $i,
            ]);
        }

        $data = $this->service->getBodyFatData($user);

        $this->assertArrayHasKey('year', $data);
        $this->assertCount(12, $data['year']['yearBodyFat']); // 月平均12件
        $this->assertNotNull($data['year']['yearAverage']);
        $this->assertCount(12, $data['year']['yearLabels']);
        $this->assertCount(12, $data['year']['yearMonths']);
    }
}
