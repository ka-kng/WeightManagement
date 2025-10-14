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
    Route::PATCH('/mypage', [MypageController::class, 'update'])->name('mypage.update');
    Route::DELETE('/mypage', [MypageController::class, 'destroy'])->name('mypage.destroy');
    Route::resource('records', RecordController::class);
});

require __DIR__ . '/auth.php';
