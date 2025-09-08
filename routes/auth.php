<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
// メール再送
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    // ログアウト

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');


    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});


/*
|--------------------------------------------------------------------------
| Email Verification Route (No Auth Middleware)
|--------------------------------------------------------------------------
| 未ログインでもリンクを踏めるよう auth ミドルウェアは外す
*/

Route::get('verify-email/{id}/{hash}', function ($id, $hash) {
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
    }

    return redirect()->route('login')->with('verified', true);
})->middleware('signed', 'throttle:6,1')
    ->name('verification.verify');
