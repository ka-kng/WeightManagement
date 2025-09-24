<?php

use App\Http\Controllers\HomeBmiController;
use App\Http\Controllers\HomeBodyfatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomeSleepController;
use App\Http\Controllers\HomeWeightController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\RecordComparisonController;
use App\Http\Controllers\RecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\URL;



Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('/home/weight', [HomeWeightController::class, 'show'])->name('home.weight');
    Route::get('/home/bmi', [HomeBmiController::class, 'show'])->name('home.bmi');
    Route::get('/home/bodyfat', [HomeBodyfatController::class, 'show'])->name('home.bodyfat');
    Route::get('/home/sleep', [HomeSleepController::class, 'show'])->name('home.sleep');
    Route::get('/comparison', [RecordComparisonController::class, 'latest'])->name('comparison.latest');
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');
    Route::PUT('/mypage', [MypageController::class, 'update'])->name('mypage.update');
    Route::resource('records', RecordController::class);
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
