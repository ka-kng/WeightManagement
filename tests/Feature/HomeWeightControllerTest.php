<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\HomeWeightService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class HomeWeightControllerTest extends TestCase
{
    use RefreshDatabase;

    // HomeWeightController の show メソッドが正しくビューを返し、
    // ビューに必要な体重データが渡されているかを確認するテスト
    public function test_show_returns_view_with_weight_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mockService = Mockery::mock(HomeWeightService::class);
        $mockService->shouldReceive('getWeightData')
            ->once()
            ->with($user)
            ->andReturn([
                'week' => [
                    'weights' => [60, 61],
                    'average' => 60.5,
                    'labels' => ['Mon', 'Tue'],
                    'days' => [1, 2],
                ],
                'month' => [
                    'weights' => [60, 62],
                    'average' => 61,
                    'labels' => ['Week1', 'Week2'],
                    'fullPeriodLabel' => 'Oct 1-14',
                ],
                'year' => [
                    'weights' => [61, 60.5],
                    'average' => 60.75,
                    'labels' => ['Jan', 'Feb'],
                    'days' => [1, 2],
                ],
            ]);

        $this->app->instance(HomeWeightService::class, $mockService);

        $response = $this->get(route('home.weight'));

        $response->assertStatus(200)
            ->assertViewIs('home.chart.weight')
            ->assertViewHasAll([
                'weekLabels',
                'weekDays',
                'weekWeights',
                'weekAverage',
                'monthLabels',
                'monthWeights',
                'fullPeriodLabel',
                'monthAverage',
                'yearLabels',
                'yearDays',
                'yearWeights',
                'yearAverage',
            ]);
    }
}
