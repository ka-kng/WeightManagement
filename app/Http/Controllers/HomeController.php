<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    // 性別定数
    private const GENDER_MALE = 1;
    private const GENDER_FEMALE = 2;

    // BMI・体脂肪率計算定数
    private const BODY_FAT_BASE        = 3.02;
    private const BODY_FAT_WEIGHT_COEFF = 0.461;
    private const BODY_FAT_HEIGHT_COEFF = 0.089;
    private const BODY_FAT_AGE_COEFF    = 0.038;
    private const BODY_FAT_CONST        = -0.238;
    private const BODY_FAT_MALE_ADJUST  = -6.85;

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

            $genderValue = ($user->gender == self::GENDER_MALE) ? 1 : 0;

            $bodyFatNumerator =
                self::BODY_FAT_BASE +
                self::BODY_FAT_WEIGHT_COEFF * $record->weight -
                self::BODY_FAT_HEIGHT_COEFF * $user->height +
                self::BODY_FAT_AGE_COEFF * $age +
                self::BODY_FAT_CONST;

            if ($user->gender == self::GENDER_MALE) { // 男性補正
                $bodyFatNumerator += self::BODY_FAT_MALE_ADJUST;
            }

            $record->body_fat = ($bodyFatNumerator / $record->weight) * 100;
        };

        return view('home.index', compact('record'));
    }

}
