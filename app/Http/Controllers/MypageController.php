<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('mypage.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'height' => ['required', 'numeric'],
            'target_weight' => ['required', 'numeric'],
            'gender' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()->route('mypage.index')->with('success', 'プロフィールを更新しました');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        // ログアウト
        Auth::logout();

        // ユーザーを削除
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ホームページにリダイレクト
        return redirect('/')->with('status', 'アカウントが削除されました');
    }
}
