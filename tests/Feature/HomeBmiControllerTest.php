<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeBmiControllerTest extends TestCase
{
    use RefreshDatabase;

    // HomeBmiController show() が正しいビューとデータを返すかのテスト
    public function test_show_returns_view_with_bmi_data()
    {
        $user = User::factory()->create([
            'height' => 170, // テスト用身長
        ]);

        $this->actingAs($user);

        $response = $this->get(route('home.bmi')); // ルート名に合わせて修正

        $response->assertStatus(200);
        $response->assertViewIs('home.chart.bmi');

        $response->assertViewHasAll([
            'weekLabels',
            'weekDays',
            'weekBmis',
            'weekAverage',
            'monthLabels',
            'monthDays',
            'monthBmis',
            'monthAverage',
            'fullPeriodLabel',
            'yearLabels',
            'yearDays',
            'yearBmis',
            'yearAverage',
        ]);
    }
}
