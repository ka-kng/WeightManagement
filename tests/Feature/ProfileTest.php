<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    //プロフィールページが正しく表示
    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/mypage');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/mypage', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'birth_date' => '1990-01-01',
                'height' => 170,
                'target_weight' => 65,
                'gender' => '0',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/mypage');

        $user->refresh();
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('1990-01-01', $user->birth_date->toDateString());
        $this->assertEquals(170, $user->height);
        $this->assertEquals(65, $user->target_weight);
        $this->assertEquals('0', $user->gender);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/mypage', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'birth_date' => '1990-01-01',
                'height' => 170,
                'target_weight' => 65,
                'gender' => '0',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/mypage');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        // ユーザーを作成し、パスワードは 'password' に設定（ハッシュ化して保存）
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // 作成したユーザーでログインした状態で、アカウント削除リクエストを送信
        // リクエストには現在のパスワードを送る（current_password バリデーション用）
        $this->actingAs($user)
            ->delete(route('mypage.destroy'), [
                'password' => 'password',
            ]);

        // ユーザーがデータベースから削除されていることを確認
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        // User::find() でも null になることを確認し、削除されたことを検証
        $this->assertNull(User::find($user->id));
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/mypage')
            ->delete('/mypage', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/mypage');

        $this->assertNotNull($user->fresh());
    }
}
