<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    // 性別定数
    private const GENDER_MALE = 1;
    private const GENDER_FEMALE = 2;

    // BMI・体脂肪率計算定数
    private const BMI_COEFF = 1.2;
    private const AGE_COEFF = 0.23;
    private const BODY_FAT_CONST_MALE = 16.2;
    private const BODY_FAT_CONST_FEMALE = 5.4;

    // 睡眠計算定数
    private const MINUTES_PER_HOUR = 60;

    public function index()
    {
        $record = Record::with('user')
            ->where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->first();

        if ($record) {
            $user = $record->user;
            $age = $user->birth_date->age;

            // BMI計算
            $record->bmi = $record->weight / (($user->height / 100) ** 2);

            // 体脂肪率計算（簡易）
            $record->body_fat = $user->gender == self::GENDER_MALE
                ? (self::BMI_COEFF * $record->bmi + self::AGE_COEFF * $age - self::BODY_FAT_CONST_MALE)
                : (self::BMI_COEFF * $record->bmi + self::AGE_COEFF * $age - self::BODY_FAT_CONST_FEMALE);

            // 睡眠時間を分に変換
            $record->total_sleep_minutes = $record->sleep_hours * self::MINUTES_PER_HOUR + $record->sleep_minutes;

        };

        return view('home.index', compact('record'));
    }

    public function create()
    {
        //
    }
}
