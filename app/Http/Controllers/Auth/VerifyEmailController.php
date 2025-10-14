<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;

class VerifyEmailController extends Controller
{
    public function __invoke($id, $hash)
    {
        $user = User::findOrFail($id);

        // URL署名チェック（改ざん防止）
        if (! URL::hasValidSignature(request())) {
            abort(403, '無効なリンクです');
        }

        // ハッシュチェック（本人確認）
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, '無効なリンクです');
        }

        // 未認証なら更新
        if (! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
            $user->save();

            // イベントを発生させる
            event(new Verified($user));
        }

        // メール認証が成功した後のリダイレクト
        return redirect()->route('login')->with('verified', true);
    }
}
