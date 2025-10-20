<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\HomeSleepService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class HomeSleepControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_view_with_sleep_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // -------------------------------
        $mockService = Mockery::mock(HomeSleepService::class);
        $mockService->shouldReceive('getSleepData')
            ->once()
            ->with($user)
            ->andReturn([
                'week' => [
                    'weekSleep' => [7, 6.5],      // Blade で使用される変数
                    'weekAverage' => 6.75,
                    'weekLabels' => ['Mon', 'Tue'],
                    'weekDays' => [1, 2],
                ],
                'month' => [
                    'monthSleep' => [7, 6],       // Blade で使用される変数
                    'monthAverage' => 6.5,
                    'monthLabels' => ['Week1', 'Week2'],
                    'monthDays' => [7, 6],
                    'fullPeriodLabel' => 'Oct 1-14',
                ],
                'year' => [
                    'yearSleep' => [6.5, 6.8],   // Blade で使用される変数
                    'yearAverage' => 6.65,
                    'yearLabels' => ['Jan', 'Feb'],
                    'yearMonths' => [1, 2],
                    'yearDays' => [1, 2],
                ],
            ]);

        // Laravel のサービスコンテナにモックをバインド
        $this->app->instance(HomeSleepService::class, $mockService);

        $response = $this->get(route('home.sleep'));

        // ステータスコードが 200 か
        $response->assertStatus(200);

        // 正しいビューが返っているか
        $response->assertViewIs('home.chart.sleep');

        // ビューに期待される変数が渡されているか
        $response->assertViewHasAll([
            'weekSleep',
            'weekAverage',
            'weekLabels',
            'weekDays',
            'monthSleep',
            'monthAverage',
            'monthLabels',
            'monthDays',
            'fullPeriodLabel',
            'yearSleep',
            'yearAverage',
            'yearLabels',
            'yearMonths',
        ]);
    }
}
