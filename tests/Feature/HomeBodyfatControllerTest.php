<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\HomeBodyfatService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class HomeBodyfatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_view_with_bodyfat_data()
    {
        // テスト用ユーザーを作成しログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // HomeBodyfatService をモック化して固定の返却データを指定
        $mockService = Mockery::mock(HomeBodyfatService::class);
        $mockService->shouldReceive('getBodyfatData')
            ->once() // 一度だけ呼ばれることを期待
            ->with($user) // 引数に作成したユーザーが渡されることを期待
            ->andReturn([
                'week' => [
                    'weekLabels' => ['Mon'],
                    'weekDays' => [1],
                    'weekBodyFat' => [15.5],
                    'weekAverage' => 15.5
                ],
                'month' => [
                    'monthLabels' => ['Week1'],
                    'monthDaysBodyFat' => [1],
                    'monthBodyFat' => [15.5],
                    'monthAverage' => 15.5,
                    'fullPeriodLabel' => 'Jan 1-7'
                ],
                'year' => [
                    'yearLabels' => ['Jan'],
                    'yearMonths' => [1],
                    'yearBodyFat' => [15.5],
                    'yearAverage' => 15.5
                ],
            ]);
        // モックをアプリケーションコンテナにバインド
        $this->app->instance(HomeBodyfatService::class, $mockService);

        // /home/bodyfat ルートに GET リクエスト
        $response = $this->get(route('home.bodyfat'));

        // レスポンスが 200 OK であることを確認
        $response->assertStatus(200);

        // 正しいビューが返っていることを確認
        $response->assertViewIs('home.chart.bodyfat');

        // ビューに正しいデータが渡されていることを確認
        $response->assertViewHasAll([
            'weekLabels',
            'weekDays',
            'weekBodyFat',
            'weekAverage',
            'monthLabels',
            'monthDaysBodyFat',
            'monthBodyFat',
            'monthAverage',
            'fullPeriodLabel',
            'yearLabels',
            'yearMonths',
            'yearBodyFat',
            'yearAverage',
        ]);
    }
}
