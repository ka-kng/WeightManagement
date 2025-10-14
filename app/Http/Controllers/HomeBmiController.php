<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeBmiController extends Controller
{
    private const DAYS_IN_WEEK = 7;
    private const WEEKS_IN_MONTH = 4;
    private const DAYS_IN_MONTH = self::DAYS_IN_WEEK * self::WEEKS_IN_MONTH;
    private const MONTHS_IN_YEAR = 12;

    public function show()
    {
        $user = Auth::user();
        $height = $user->height / 100;
        $today = Carbon::today();

        [$weekLabels, $weekDays, $weekBmis, $weekAverage] = $this->getWeekData($user->id, $height, $today);

        [$monthLabels, $monthDays, $monthBmis, $monthAverage, $fullPeriodLabel] = $this->getMonthData($user->id, $height, $today);

        [$yearLabels, $yearDays, $yearBmis, $yearAverage] = $this->getYearData($user->id, $height, $today);

        return view('home.chart.bmi', compact(
            'weekLabels',
            'weekDays',
            'weekBmis',
            'weekAverage',
            'monthLabels',
            'monthDays',
            'monthBmis',
            'monthAverage',
            'fullPeriodLabel',
            'yearLabels',
            'yearDays',
            'yearBmis',
            'yearAverage'
        ));
    }

    private function getWeekData(int $userId, float $height, Carbon $today)
    {
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$today->copy()->subDays(6), $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $labels = $days = $bmis = [];

        for ($date = $today->copy()->subDays(self::DAYS_IN_WEEK - 1); $date->lte($today); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $weight = $records[$key]->weight ?? null;

            $labels[] = $date->format('n月j日');
            $days[] = $date->format('j日');
            $bmis[] = $this->calcBmi($weight, $height);
        }

        return [$labels, $days, $bmis, $this->calcAverage($bmis)];
    }

    private function getMonthData(int $userId, float $height, Carbon $today)
    {
        $periodStart = $today->copy()->subDays(self::DAYS_IN_MONTH - 1);
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$periodStart, $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $labels = [];
        $days = [];
        $bmis = [];

        for ($i = 0; $i < self::WEEKS_IN_MONTH; $i++) {
            $start = $periodStart->addDays($i * self::DAYS_IN_WEEK);
            $end = $start->copy()->addDays(self::DAYS_IN_WEEK - 1);

            $weekBmis = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->format('Y-m-d');
                if (isset($records[$key])) {
                    $weekBmis[] = $this->calcBmi($records[$key]->weight, $height);
                }
            }

            $labels[] = $start->format('n/j') . '～' . $end->format('n/j');
            $bmis[] = $this->calcAverage($weekBmis);
        }

        $monthAverage = $this->calcAverage($bmis);
        $fullPeriodLabel = $periodStart->format('n月j日') . '～' . $end->format('n月j日');

        return [$labels, $days, $bmis, $monthAverage, $fullPeriodLabel];
    }

    private function getYearData(int $userId, float $height, Carbon $today)
    {
        $start = $today->copy()->subMonths(self::MONTHS_IN_YEAR - 1)->startOfMonth();
        $end = $today->copy()->endOfMonth();

        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        $labels = [];
        $days = [];
        $bmis = [];

        for ($i = 0; $i < self::MONTHS_IN_YEAR; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('Y年n月');
            $days[] = $month->format('n月');

            if (isset($records[$key])) {
                $weights = $records[$key]->pluck('weight')->toArray();
                $monthBmis = array_map(fn($w) => $this->calcBmi($w, $height), $weights);
                $bmis[] = $this->calcAverage($monthBmis);
            } else {
                $bmis[] = null;
            }
        }

        return [$labels, $days, $bmis, $this->calcAverage($bmis)];
    }

    private function calcBmi(?float $weight, float $height): ?float
    {
        return $weight ? round($weight / ($height * $height), 2) : null;
    }

    private function calcAverage(array $values): ?float
    {
        $filtered = array_filter($values, fn($v) => $v !== null);
        return !empty($filtered) ? round(array_sum($filtered) / count($filtered), 2) : null;
    }
}
