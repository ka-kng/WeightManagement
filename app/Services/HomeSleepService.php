<?php

namespace App\Services;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HomeSleepService
{
    // 期間計算用の定数
    private const DAYS_IN_WEEK = 7;
    private const WEEKS_IN_MONTH = 4;
    private const MONTHS_IN_YEAR = 12;

    // 週・月・年の睡眠データをまとめて取得
    public function getSleepData($user): array
    {
        $today = Carbon::today();

        $cacheKey = "sleep_data_user_{$user->id}";

        // 睡眠時間データを「週・月・年」単位でまとめて取得
        // キャッシュ（6時間）を使ってパフォーマンスを向上
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($user, $today) {
            $records = $this->getRecordsBetween(
                $user->id,
                $today->copy()->subYear()->addDay(),
                $today
            );

            // 週・月・年単位でデータをまとめる
            return [
                'week' => $this->getWeekData($today->copy()->subDays(self::DAYS_IN_WEEK - 1), $today, $records),
                'month' => $this->getMonthlyData($records, $today),
                'year' => $this->getYearlyData($records, $today),
            ];
        });
    }

    // 週のデータ取得
    private function getWeekData(Carbon $start, Carbon $end, $records): array
    {
        $labels = $days = $values = [];

        // 日ごとにループしてラベルと値を作成
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $labels[] = $date->format('n月d日');
            $days[] = $date->format('j日');

            $record = $records[$date->format('Y-m-d')] ?? null;
            $values[] = $record ? $this->sleepDecimal($record) : null;
        }

        return [
            "weekLabels" => $labels,
            "weekDays" => $days,
            "weekSleep" => $values,
            "weekAverage" => $this->calcAverage($values),
        ];
    }

    // 月間データ取得（直近4週間）
    private function getMonthlyData($records, Carbon $today): array
    {
        $periodStart = $today->copy()->subDays(self::DAYS_IN_WEEK * self::WEEKS_IN_MONTH - 1);

        $labels = $weeksValues = $weekAverages = [];

        // 週ごとにデータ集計
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
            $weekAverages[] = $this->calcAverage($week); // 週平均
        }

        return [
            'monthLabels' => $labels,
            'monthSleep' => $weekAverages,
            'monthAverage' => $this->calcAverage($weekAverages),
            'fullPeriodLabel' => $periodStart->format('n月j日') . '～' . $today->format('n月j日'),
        ];
    }

    // 年間データ取得（直近12ヶ月）
    private function getYearlyData($records, Carbon $today): array
    {
        $start = $today->copy()->subMonths(self::MONTHS_IN_YEAR - 1)->startOfMonth();

        // 月単位にレコードをグループ化
        $recordsByMonth = $records->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        $labels = []; // グラフ上部ラベル（例: 2025年10月）
        $months = []; // x軸用ラベル（例: 10月）
        $values = []; // 各月の平均睡眠時間

        for ($i = 0; $i < self::MONTHS_IN_YEAR; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('Y年n月');
            $months[] = $month->format('n月');

            if (isset($recordsByMonth[$key])) {
                // 月内の全日分を小数形式に変換
                $monthValues = $recordsByMonth[$key]
                    ->map(fn($r) => $this->sleepDecimal($r))
                    ->toArray();
                $values[] = $this->calcAverage($monthValues); // 月平均
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

    // 指定期間のレコード取得
    private function getRecordsBetween(int $userId, Carbon $start, Carbon $end)
    {
        return Record::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));
    }

    // 平均計算（null除外）
    private function calcAverage(array $values): ?float
    {
        $filtered = array_filter($values, fn($v) => !is_null($v));
        return !empty($filtered) ? round(array_sum($filtered) / count($filtered), 1) : null;
    }

    // レコードの睡眠時間を10進数に変換（例: 7時間30分 → 7.5）
    private function sleepDecimal($record): float
    {
        return $record->sleep_hours + ($record->sleep_minutes / 60);
    }

    // 小数形式の時間を「〇時間〇分」に変換
    public static function formatHoursMinutes($decimal)
    {
        if (is_null($decimal)) return '-';
        $hours = floor($decimal);
        $minutes = round(($decimal - $hours) * 60);
        return "{$hours}時間{$minutes}分";
    }
}
