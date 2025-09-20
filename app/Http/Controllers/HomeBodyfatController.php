<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeBodyfatController extends Controller
{
    private const GENDER_MALE = 1;
    private const BODY_FAT_BASE = 3.02;
    private const BODY_FAT_WEIGHT_COEFF = 0.461;
    private const BODY_FAT_HEIGHT_COEFF = 0.089;
    private const BODY_FAT_AGE_COEFF = 0.038;
    private const BODY_FAT_CONST = -0.238;
    private const BODY_FAT_MALE_ADJUST = -6.85;


    public function show()
    {
        $user = Auth::user();
        $userId = $user->id;
        $today = Carbon::today();

        [$weekLabels, $weekDays, $weekBodyFat, $weekAverage] = $this->getWeekData($user, $today);

        return view('home.chart.bodyfat', compact(
            'weekLabels',
            'weekDays',
            'weekBodyFat',
            'weekAverage',
            // 'monthLabels',
            // 'monthDays',
            // 'monthBodyFat',
            // 'yearLabels',
            // 'yearDays',
            // 'yearBodyFat'
        ));
    }

    private function calcBodyFat($weight, $height, $age, $gender)
    {
        $val = self::BODY_FAT_BASE
            + self::BODY_FAT_WEIGHT_COEFF * $weight
            - self::BODY_FAT_HEIGHT_COEFF * $height
            + self::BODY_FAT_AGE_COEFF * $age
            + self::BODY_FAT_CONST;

        if ($gender == self::GENDER_MALE) {
            $val += self::BODY_FAT_MALE_ADJUST;
        }

        return ($val / $weight) * 100;
    }

    private function getWeekData($user, $today)
    {
        $weekLabels = [];
        $weekDays = [];
        $weekBodyFat = [];

        $records = Record::where('user_id', $user->id)
            ->whereBetween('date', [$today->copy()->subDays(6), $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        for ($date = $today->copy()->subDays(6); $date->lte($today); $date->addDay()) {
            $weekLabels[] = $date->format('m/d');
            $weekDays[] = $date->format('j日');

            $key = $date->format('Y-m-d');
            if (isset($records[$key])) {
                $weight = $records[$key]->weight;
                $age = $user->birth_date->age;
                $weekBodyFat[] = round($this->calcBodyFat($weight, $user->height, $age, $user->gender), 1);
            } else {
                $weekBodyFat[] = null;
            }
        }

        $valid = array_filter($weekBodyFat, fn($v) => !is_null($v));
        $weekAverage = !empty($valid) ? round(array_sum($valid) / count($valid), 1) : null;

        return [$weekLabels, $weekDays, $weekBodyFat, $weekAverage];
    }
}
