<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function users_can_authenticate_using_the_login_screen(): void
    {
        // テスト用ユーザーを作成（必須フィールドすべて指定）
        $user = User::factory()->create([
            'name' => '樺澤憲悟',
            'email' => 'ka.kengo0503@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('ka.kengo0503@gmail.com'),
            'gender' => 0,
            'birth_date' => '2002-05-03',
            'height' => 168.7,
            'target_weight' => 53.5,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'ka.kengo0503@gmail.com',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    /** @test */
    public function users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'name' => '樺澤憲悟',
            'email' => 'ka.kengo0503@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('ka.kengo0503@gmail.com'),
            'gender' => 0,
            'birth_date' => '2002-05-03',
            'height' => 168.7,
            'target_weight' => 53.5,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function users_can_logout(): void
    {
        $user = User::factory()->create([
            'name' => '樺澤憲悟',
            'email' => 'ka.kengo0503@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('ka.kengo0503@gmail.com'),
            'gender' => 0,
            'birth_date' => '2002-05-03',
            'height' => 168.7,
            'target_weight' => 53.5,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
