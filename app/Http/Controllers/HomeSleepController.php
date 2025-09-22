<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeSleepController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 週データ
        [$weekLabels, $weekDays, $weekSleep, $weekAverage] = $this->getWeekData($user, $today);

        // 月データ（4週間ごとの平均）
        [$monthLabels, $monthDaysSleep, $monthSleep, $monthAverage, $fullPeriodLabel] = $this->getMonthData($user, $today);

        // 年データ（12か月ごとの平均）
        [$yearLabels, $yearDays, $yearSleep, $yearAverage] = $this->getYearData($user, $today);

        return view('home.chart.sleep', compact(
            'weekLabels', 'weekDays', 'weekSleep', 'weekAverage',
            'monthLabels', 'monthDaysSleep', 'monthSleep', 'monthAverage', 'fullPeriodLabel',
            'yearLabels', 'yearDays', 'yearSleep', 'yearAverage'
        ));
    }

    /**
     * 週データ取得
     */
    private function getWeekData($user, Carbon $today)
    {
        $weekLabels = [];
        $weekDays = [];
        $weekSleep = [];

        $records = Record::where('user_id', $user->id)
            ->whereBetween('date', [$today->copy()->subDays(6), $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        for ($date = $today->copy()->subDays(6); $date->lte($today); $date->addDay()) {
            $weekLabels[] = $date->format('n月d日');
            $weekDays[] = $date->format('j日');

            $key = $date->format('Y-m-d');
            if (isset($records[$key])) {
                $r = $records[$key];
                $weekSleep[] = $r->sleep_hours + ($r->sleep_minutes / 60);
            } else {
                $weekSleep[] = null;
            }
        }

        $valid = array_filter($weekSleep, fn($v) => !is_null($v));
        $weekAverage = !empty($valid) ? round(array_sum($valid) / count($valid), 1) : null;

        return [$weekLabels, $weekDays, $weekSleep, $weekAverage];
    }

    /**
     * 月データ取得（4週間ごとの平均）
     */
    private function getMonthData($user, Carbon $today)
    {
        $monthLabels = [];
        $monthSleep = [];
        $monthDaysSleep = [];

        $records = Record::where('user_id', $user->id)
            ->whereBetween('date', [$today->copy()->subDays(27), $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        for ($i = 0; $i < 4; $i++) {
            $start = $today->copy()->subDays(27)->addDays($i * 7);
            $end = $start->copy()->addDays(6);

            $weekSleep = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->format('Y-m-d');
                if (isset($records[$key])) {
                    $r = $records[$key];
                    $weekSleep[] = $r->sleep_hours + ($r->sleep_minutes / 60);
                } else {
                    $weekSleep[] = null;
                }
            }

            $monthDaysSleep[] = $weekSleep;
            $monthLabels[] = $start->format('n/j') . '～' . $end->format('n/j');
            $validWeek = array_filter($weekSleep, fn($v) => !is_null($v));
            $monthSleep[] = !empty($validWeek) ? round(array_sum($validWeek) / count($validWeek), 1) : null;
        }

        $fullPeriodLabel = $today->copy()->subDays(27)->format('n月j日') . '～' . $end->format('n月j日');

        $validMonth = array_filter($monthSleep, fn($v) => !is_null($v));
        $monthAverage = !empty($validMonth) ? round(array_sum($validMonth) / count($validMonth), 1) : null;

        return [$monthLabels, $monthDaysSleep, $monthSleep, $monthAverage, $fullPeriodLabel];
    }

    /**
     * 年データ取得（12か月ごとの平均）
     */
    private function getYearData($user, Carbon $today)
    {
        $yearLabels = [];
        $yearDays = [];
        $yearSleep = [];

        $records = Record::where('user_id', $user->id)
            ->whereBetween('date', [$today->copy()->subYear()->addDay(), $today])
            ->get()
            ->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        foreach (range(0, 11) as $i) {
            $month = $today->copy()->subMonths(11 - $i);
            $key = $month->format('Y-m');

            $yearLabels[] = $month->format('Y年n月');
            $yearDays[] = $month->format('n月');

            if (isset($records[$key])) {
                $weights = $records[$key]->map(fn($r) => $r->sleep_hours + ($r->sleep_minutes / 60))->toArray();
                $yearSleep[] = round(array_sum($weights) / count($weights), 2);
            } else {
                $yearSleep[] = null;
            }
        }

        $validYear = array_filter($yearSleep, fn($v) => !is_null($v));
        $yearAverage = !empty($validYear) ? round(array_sum($validYear) / count($validYear), 2) : null;

        return [$yearLabels, $yearDays, $yearSleep, $yearAverage];
    }

    /**
     * 時間＋分を「○時間○分」に変換
     */
    public static function formatHoursMinutes($decimal)
    {
        if (is_null($decimal)) return '-';
        $hours = floor($decimal);
        $minutes = round(($decimal - $hours) * 60);
        return "{$hours}時間{$minutes}分";
    }
}
