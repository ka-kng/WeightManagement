<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MypageService
{
    // 現在ログイン中のユーザーを取得
    public function getCurrentUser()
    {
        return Auth::user();
    }

    // ユーザープロフィールを更新
    public function updateProfile(array $data)
    {
        $user = Auth::user();
        $user->update($data);
        return $user;
    }

    // 現在ログイン中のユーザーを削除
    public function deleteAccount(string $password)
    {
        $user = Auth::user();

        // パスワードチェック
        if (!Hash::check($password, $user->password)) {
            throw new \InvalidArgumentException('パスワードが正しくありません');
        }

        // ログアウト
        Auth::logout();

        // ユーザー削除
        $user->delete();
    }
}
