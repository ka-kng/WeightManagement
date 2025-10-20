<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Services\MypageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MypageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MypageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MypageService();
    }

    // 現在ログイン中ユーザー取得テスト
    // Auth::user() が正しく返ることを確認
    public function test_get_current_user_returns_authenticated_user()
    {
        $user = User::factory()->create();
        $this->be($user); // ログイン状態にする

        $currentUser = $this->service->getCurrentUser();

        $this->assertEquals($user->id, $currentUser->id);
    }

    // ユーザープロフィール更新テスト
    // updateProfile で名前やメールが更新されることを確認
    public function test_update_profile_updates_user_data()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $this->be($user);

        $updatedData = [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ];

        $updatedUser = $this->service->updateProfile($updatedData);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('new@example.com', $updatedUser->email);
    }

    // ユーザー削除テスト
    // deleteAccount で正しいパスワードならユーザーが削除されることを確認
    public function test_delete_account_deletes_user_with_correct_password()
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);
        $this->be($user);

        $this->service->deleteAccount($password);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertGuest(); // ログアウトされていることも確認
    }

    // ユーザー削除失敗テスト
    // deleteAccount で間違ったパスワードなら例外が投げられることを確認
    public function test_delete_account_throws_exception_with_incorrect_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct_password'),
        ]);
        $this->be($user);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('パスワードが正しくありません');

        $this->service->deleteAccount('wrong_password');
    }
}
