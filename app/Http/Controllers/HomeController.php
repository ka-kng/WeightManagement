<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    private const GENDER_MALE = 1;
    private const GENDER_FEMALE = 2;

    // 体脂肪率計算定数
    private const BODY_FAT_BASE         = 3.02;
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
            $record->bmi      = $this->calcBmi($record->weight, optional($record->user)->height);
            $record->body_fat = $this->calcBodyFat($record->weight, optional($record->user)->height, optional($record->user)->birth_date?->age, optional($record->user)->gender);
        }

        return view('home.index', compact('record'));
    }

    private function calcBmi(?float $weight, ?float $height): ?float
    {
        if (!$weight || !$height) return null;
        return round($weight / (($height / 100) ** 2), 1);
    }

    private function calcBodyFat(?float $weight, ?float $height, ?int $age, ?int $gender): ?float
    {
        if (!$weight || !$height || !$age || !$gender) return null;

        $val = self::BODY_FAT_BASE
            + self::BODY_FAT_WEIGHT_COEFF * $weight
            - self::BODY_FAT_HEIGHT_COEFF * $height
            + self::BODY_FAT_AGE_COEFF * $age
            + self::BODY_FAT_CONST;

        if ($gender === self::GENDER_MALE) {
            $val += self::BODY_FAT_MALE_ADJUST;
        }

        return round(($val / $weight) * 100, 1);
    }
}
