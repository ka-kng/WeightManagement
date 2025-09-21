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

        [$monthLabels, $monthDays, $monthBodyFat, $monthAverage, $fullPeriodLabel] = $this->getMonthData($user, $today);

        [$yearLabels, $yearMonths, $yearBodyFat, $yearAverage] = $this->getYearData($user, $today);

        return view('home.chart.bodyfat', compact(
            'weekLabels',
            'weekDays',
            'weekBodyFat',
            'weekAverage',
            'monthLabels',
            'monthDays',
            'monthBodyFat',
            'monthAverage',
            'fullPeriodLabel',
            'yearLabels',
            'yearMonths',
            'yearBodyFat',
            'yearAverage',
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
            $weekLabels[] = $date->format('n月d日');
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

    private function getMonthData($user, $today)
    {
        $monthLabels = [];
        $monthBodyFat = [];
        $monthDaysBodyFat = [];

        $records = Record::where('user_id', $user->id)
            ->whereBetween('date', [$today->copy()->subDays(27), $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        for ($i = 0; $i < 4; $i++) {
            $start = $today->copy()->subDays(27)->addDays($i * 7);
            $end = $start->copy()->addDays(6);

            $weekBodyFat = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->format('Y-m-d');
                if (isset($records[$key])) {
                    $weight = $records[$key]->weight;
                    $age = $user->birth_date->age;
                    $weekBodyFat[] = round($this->calcBodyFat($weight, $user->height, $age, $user->gender), 1);
                } else {
                    $weekBodyFat[] = null;
                }
            }
            $monthDaysBodyFat[] = $weekBodyFat;
            $monthLabels[] = $start->format('n/j') . '～' . $end->format('n/j');
            $valid = array_filter($weekBodyFat, fn($v) => !is_null($v));
            $monthBodyFat[] = !empty($valid) ? round(array_sum($valid) / count($valid), 1) : null;
        }

        $fullPeriodLabel = $today->copy()->subDays(27)->format('n月j日') . '～' . $end->format('n月j日');

        $validMonth = array_filter($monthBodyFat, fn($v) => !is_null($v));
        $monthAverage = !empty($validMonth) ? round(array_sum($validMonth) / count($validMonth), 1) : null;

        return [$monthLabels, $monthDaysBodyFat, $monthBodyFat, $monthAverage, $fullPeriodLabel];
    }

    private function getYearData($user, $today)
    {
        $yearLabels = [];
        $yearBodyFat = [];
        $yearMonths = [];

        $records = Record::where('user_id', $user->id)
            ->whereBetween('date', [$today->copy()->subYear()->addDay(), $today])
            ->get()
            ->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        foreach (range(0, 11) as $i) {
            $month = $today->copy()->subMonths(11 - $i);
            $key = $month->format('Y-m');

            $yearLabels[] = $month->format('Y年n月');
            $yearMonths[] = $month->format('n月');

            if (isset($records[$key])) {
                $weights = $records[$key]->pluck('weight')->toArray();
                $bodyFats = array_map(fn($w) => round($this->calcBodyFat($w, $user->height, $user->birth_date->age, $user->gender), 1), $weights);
                $valid = array_filter($bodyFats, fn($v) => !is_null($v));
                $yearBodyFat[] = !empty($valid) ? round(array_sum($valid) / count($valid), 1) : null;
            } else {
                $yearBodyFat[] = null;
            }
        }

        $validYear = array_filter($yearBodyFat, fn($v) => !is_null($v));
        $yearAverage = !empty($validYear) ? round(array_sum($validYear) / count($validYear), 1) : null;

        return [$yearLabels, $yearMonths, $yearBodyFat, $yearAverage];
    }
}
