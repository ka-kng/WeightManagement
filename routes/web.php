<?php

use App\Http\Controllers\RecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\URL;



Route::middleware('auth')->group(function () {
    Route::prefix('records')->name('records.')->group(function () {
        Route::get('/register', [RecordController::class, 'create'])->name('create');
        Route::post('/register', [RecordController::class, 'store'])->name('store');
    });
});

Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);

    // URL署名チェック
    if (! URL::hasValidSignature(request())) {
        abort(403, '無効なリンクです');
    }

    // ハッシュチェック
    if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, '無効なリンクです');
    }

    // 未認証なら更新
    if (! $user->hasVerifiedEmail()) {
        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }

    return redirect()->route('record')->with('verified', true);
})->middleware('signed') // auth は不要
    ->name('verification.verify');


// 認証メール再送
Route::post('/email/verification-resend', function(Request $request){
    $request->validate(['email'=>'required|email']);
    $user = \App\Models\User::where('email', $request->email)->first();
    if ($user && !$user->hasVerifiedEmail()) {
        $user->sendEmailVerificationNotification();
        return back()->with('message','認証メールを再送しました');
    }
    return back()->with('message','対象のユーザーが存在しないか既に認証済みです');
})->name('verification.resend.guest');

require __DIR__ . '/auth.php';
