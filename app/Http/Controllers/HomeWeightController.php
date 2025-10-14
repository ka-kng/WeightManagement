<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HomeWeightController extends Controller
{
    private const DAYS_IN_WEEK = 7;
    private const WEEKS_IN_MONTH = 4;
    private const DAYS_IN_MONTH = self::DAYS_IN_WEEK * self::WEEKS_IN_MONTH;
    private const MONTHS_IN_YEAR = 12;

    public function show()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // 週
        [$weekLabels, $weekDays, $weekWeights, $weekAverage] = $this->getWeekData($userId, $today);

        // 月（4週間ごとの平均を取得）
        [$monthLabels, $monthWeights, $fullPeriodLabel, $monthAverage] = $this->getMonthData($userId, $today);

        // 年（12か月ごとの平均）
        [$yearLabels, $yearDays, $yearWeights, $yearAverage] = $this->getYearData($userId, $today);

        return view('home.chart.weight', compact(
            'weekLabels',
            'weekDays',
            'weekWeights',
            'weekAverage',
            'monthLabels',
            'monthWeights',
            'fullPeriodLabel',
            'monthAverage',
            'yearLabels',
            'yearDays',
            'yearWeights',
            'yearAverage'
        ));
    }

    private function getWeekData($userId, Carbon $today)
    {
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$today->copy()->subDays(6), $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $weekLabels = [];
        $weekDays = [];
        $weekWeights = [];

        for ($date = $today->copy()->subDays(6); $date->lte($today); $date->addDay()) {
            $key = $date->format('Y-m-d');

            $weekLabels[] = $date->format('n月d日'); // 上部表示
            $weekDays[] = $date->format('j日');      // x軸用
            $weekWeights[] = $records[$key]->weight ?? null;
        }

        return [$weekLabels, $weekDays, $weekWeights, $this->calcAverage($weekWeights)];
    }

    private function getMonthData($userId, Carbon $today)
    {
        $periodStart = $today->copy()->subDays(self::DAYS_IN_MONTH - 1);
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$periodStart, $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $monthLabels = [];
        $monthWeights = [];

        for ($i = 0; $i < self::WEEKS_IN_MONTH; $i++) {
            $start = $periodStart->copy()->addDays($i * self::DAYS_IN_WEEK);
            $end = $start->copy()->addDays(self::DAYS_IN_WEEK - 1);

            $weekWeights = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->format('Y-m-d');
                if (isset($records[$key])) {
                    $weekWeights[] = $records[$key]->weight;
                }
            }

            $monthLabels[] = $start->format('n/d') . '～' . $end->format('n/d');
            $monthWeights[] = $this->calcAverage($weekWeights);
        }

        $fullPeriodLabel = $periodStart->format('n月d日') . ' ～ ' . $end->format('n月d日');
        $monthAverage = $this->calcAverage($monthWeights);

        return [$monthLabels, $monthWeights, $fullPeriodLabel, $monthAverage];
    }

    private function getYearData($userId, Carbon $today)
    {
        $start = $today->copy()->subMonths(self::MONTHS_IN_YEAR - 1)->startOfMonth();
        $end = $today->copy()->endOfMonth();

        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        $yearLabels = [];
        $yearDays = [];
        $yearWeights = [];

        for ($i = 0; $i < self::MONTHS_IN_YEAR; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $yearLabels[] = $month->format('Y年n月');
            $yearDays[] = $month->format('n月');

            if (isset($records[$key])) {
                $weights = $records[$key]->pluck('weight')->toArray();
            } else {
                $weights = [];
            }
            $yearWeights[] = $this->calcAverage($weights);
        }

        $yearAverage = $this->calcAverage($yearWeights);

        return [$yearLabels, $yearDays, $yearWeights, $yearAverage];
    }

    private function calcAverage(array $value)
    {
        $filtered = array_filter($value, fn($v) => $v !== null);
        return !empty($filtered) ? round(array_sum($filtered) / count($filtered), 1) : null;
    }
}
