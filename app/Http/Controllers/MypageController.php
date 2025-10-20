<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\MypageUpdateRequest;
use App\Services\MypageService;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    private MypageService $mypageService;

    public function __construct(MypageService $mypageService)
    {
        $this->mypageService = $mypageService;
    }

    // マイページプロフィール画面
    public function index()
    {
        $user = $this->mypageService->getCurrentUser();
        return view('mypage.profile', compact('user'));
    }

    // プロフィール更新
    public function update(MypageUpdateRequest $request)
    {
        $data = $request->validated();
        $this->mypageService->updateProfile($data);

        return redirect()->route('mypage.index')
            ->with('success', 'プロフィールを更新しました');
    }

    // アカウント削除
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $password = $request->input('password');

        // サービスに削除処理を委譲（パスワードチェック含む）
        $this->mypageService->deleteAccount($password);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'アカウントが削除されました');
    }
}
