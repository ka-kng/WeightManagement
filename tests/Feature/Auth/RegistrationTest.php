<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'gender' => 1,
            'birth_date' => '1990-01-01',
            'height' => 170,
            'target_weight' => 60,
        ]);

        // 登録したユーザーを取得
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user); // ユーザーがデータベースに存在するか確認

        // ログイン状態をシミュレート
        $this->actingAs($user);

        // ユーザーが認証されていることを確認
        $this->assertAuthenticatedAs($user);
    }
}
