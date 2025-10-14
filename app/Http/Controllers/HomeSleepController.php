<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeSleepController extends Controller
{
    private const DAYS_IN_WEEK = 7;
    private const WEEKS_IN_MONTH = 4;
    private const MONTHS_IN_YEAR = 12;

    public function show()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 年間データをまとめて取得
        $records = $this->getRecordsBetween($user->id, $today->copy()->subYear()->addDay(), $today);

        $weekData = $this->getPeriodData($user, $today->copy()->subDays(self::DAYS_IN_WEEK - 1), $today, $records, 'week');
        $monthData = $this->getMonthlyData($user, $records, $today);
        $yearData = $this->getYearlyData($user, $records, $today);

        return view('home.chart.sleep', array_merge($weekData, $monthData, $yearData));
    }

    private function getPeriodData($user, Carbon $start, Carbon $end, $records, string $prefix): array
    {
        $labels = $days = $values = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $labels[] = $date->format('n月d日');
            $days[] = $date->format('j日');

            $record = $records[$date->format('Y-m-d')] ?? null;
            $values[] = $record ? $this->sleepDecimal($record) : null;
        }

        return [
            "{$prefix}Labels" => $labels,
            "{$prefix}Days" => $days,
            "{$prefix}Sleep" => $values,
            "{$prefix}Average" => $this->calcAverage($values),
        ];
    }

    private function getMonthlyData($user, $records, Carbon $today): array
    {
        $periodStart = $today->copy()->subDays(self::DAYS_IN_WEEK * self::WEEKS_IN_MONTH - 1);

        $labels = $weeksValues = $weekAverages = [];

        for ($i = 0; $i < self::WEEKS_IN_MONTH; $i++) {
            $start = $periodStart->copy()->addDays($i * self::DAYS_IN_WEEK);
            $end = $start->copy()->addDays(self::DAYS_IN_WEEK - 1);

            $week = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $record = $records[$d->format('Y-m-d')] ?? null;
                $week[] = $record ? $this->sleepDecimal($record) : null;
            }

            $weeksValues[] = $week;
            $labels[] = $start->format('n/j') . '～' . $end->format('n/j');
            $weekAverages[] = $this->calcAverage($week);
        }

        return [
            'monthLabels' => $labels,
            'monthDaysSleep' => $weeksValues,
            'monthSleep' => $weekAverages,
            'monthAverage' => $this->calcAverage($weekAverages),
            'fullPeriodLabel' => $periodStart->format('n月j日') . '～' . $today->format('n月j日'),
        ];
    }


    private function getYearlyData($user, $records, Carbon $today): array
    {
        $start = $today->copy()->subMonths(self::MONTHS_IN_YEAR - 1)->startOfMonth();

        $recordsByMonth = $records->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        $labels = $months = $values = [];

        for ($i = 0; $i < self::MONTHS_IN_YEAR; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('Y年n月');
            $months[] = $month->format('n月');

            if (isset($recordsByMonth[$key])) {
                $monthValues = $recordsByMonth[$key]
                    ->map(fn($r) => $this->sleepDecimal($r))
                    ->toArray();
                $values[] = $this->calcAverage($monthValues);
            } else {
                $values[] = null;
            }
        }

        return [
            'yearLabels' => $labels,
            'yearDays' => $months,
            'yearSleep' => $values,
            'yearAverage' => $this->calcAverage($values),
        ];
    }

    private function getRecordsBetween(int $userId, Carbon $start, Carbon $end)
    {
        return Record::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));
    }

    private function calcAverage(array $values): ?float
    {
        $filtered = array_filter($values, fn($v) => !is_null($v));
        return !empty($filtered) ? round(array_sum($filtered) / count($filtered), 1) : null;
    }


    private function sleepDecimal($record): float
    {
        return $record->sleep_hours + ($record->sleep_minutes / 60);
    }

    public static function formatHoursMinutes($decimal)
    {
        if (is_null($decimal)) return '-';
        $hours = floor($decimal);
        $minutes = round(($decimal - $hours) * 60);
        return "{$hours}時間{$minutes}分";
    }
}
