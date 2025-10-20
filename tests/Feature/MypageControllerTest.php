<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\MypageService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class MypageControllerTest extends TestCase
{
    use RefreshDatabase;

    // indexメソッドのテスト
    // プロフィール画面が表示され、ビューにユーザー情報が渡っているか
    public function test_index_displays_profile_with_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mockService = Mockery::mock(MypageService::class);
        $mockService->shouldReceive('getCurrentUser')
            ->once()
            ->andReturn($user);

        $this->app->instance(MypageService::class, $mockService);

        $response = $this->get(route('mypage.index'));

        $response->assertStatus(200)
            ->assertViewIs('mypage.profile')
            ->assertViewHas('user', $user);
    }

    // updateメソッドのテスト
    // プロフィール更新処理が呼ばれ、リダイレクトされるか
    public function test_update_profile_redirects_with_success_message()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mockService = Mockery::mock(MypageService::class);
        $mockService->shouldReceive('updateProfile')
            ->once()
            ->with([
                'name' => 'Test Name',
                'email' => 'test@example.com',
                'birth_date' => '1990-01-01',
                'height' => 175,
                'target_weight' => 70,
                'gender' => 'male',
            ]);

        $this->app->instance(MypageService::class, $mockService);

        $response = $this->patch(route('mypage.update'), [
            'name' => 'Test Name',
            'email' => 'test@example.com',
            'birth_date' => '1990-01-01',
            'height' => 175,
            'target_weight' => 70,
            'gender' => 'male',
        ]);

        $response->assertRedirect(route('mypage.index'))
            ->assertSessionHas('success', 'プロフィールを更新しました');
    }

    // destroyメソッドのテスト
    // アカウント削除処理が呼ばれ、セッションが破棄されリダイレクトされるか
    public function test_destroy_account_redirects_with_status()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);
        $this->actingAs($user);

        $mockService = Mockery::mock(MypageService::class);
        $mockService->shouldReceive('deleteAccount')
            ->once()
            ->with('password123');

        $this->app->instance(MypageService::class, $mockService);

        $response = $this->delete(route('mypage.destroy'), [
            'password' => 'password123',
        ]);

        $response->assertRedirect('/')
            ->assertSessionHas('status', 'アカウントが削除されました');
    }
}
