<?php

<<<<<<< HEAD
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


Route::middleware('auth')->group(function () {
    Route::get('/record', function () {
        return view('record.recordList');
    })->name('record.index');
});

=======
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\URL;

Route::get('/', function () {
    return view('auth/register');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

>>>>>>> 4cf5f76 (メール認証)
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

<<<<<<< HEAD

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
=======
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
>>>>>>> 4cf5f76 (メール認証)

require __DIR__ . '/auth.php';
