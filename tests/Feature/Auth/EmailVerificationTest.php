<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_can_be_verified(): void
    {
        // 未認証のユーザーを作成
        $user = User::factory()->unverified()->create();

        // イベントをモック
        Event::fake();

        // ログイン状態を作成
        $this->actingAs($user);

        // 認証用URLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証用URLにアクセス
        $response = $this->get($verificationUrl);

        // メール認証が成功しているかを確認
        $user->refresh(); // 最新状態を取得
        $this->assertTrue($user->hasVerifiedEmail());

        Event::assertDispatched(Verified::class);

        // 正しいリダイレクト先にリダイレクトされているかを確認
        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
