<?php

namespace App\Services;

use App\Models\Record;
use Carbon\Carbon;

class HomeBmiService
{
    // 1週間の日数・1ヶ月を4週間とした日数・1年の月数
    private const DAYS_IN_WEEK = 7;
    private const WEEKS_IN_MONTH = 4;
    private const DAYS_IN_MONTH = self::DAYS_IN_WEEK * self::WEEKS_IN_MONTH;
    private const MONTHS_IN_YEAR = 12;

    // BMIデータを「週・月・年」単位で取得する
    public function getBmiData(int $userId, float $height)
    {
        $today = Carbon::today();

        return [
            'week' => $this->getWeekData($userId, $height, $today),   // 直近1週間分
            'month' => $this->getMonthData($userId, $height, $today), // 直近4週間分
            'year' => $this->getYearData($userId, $height, $today),   // 直近1年分
        ];
    }

    // 直近1週間のBMIデータを取得
    private function getWeekData(int $userId, float $height, Carbon $today)
    {
        // 指定ユーザーの直近7日分のデータを取得（今日含む）
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$today->copy()->subDays(self::DAYS_IN_WEEK - 1), $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d')); // 日付をキーにして管理

        $labels = $days = $bmis = [];

        // 7日分のデータをループ
        for ($date = $today->copy()->subDays(self::DAYS_IN_WEEK - 1); $date->lte($today); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $weight = $records[$key]->weight ?? null; // 該当日の体重を取得

            $labels[] = $date->format('n月j日');
            $days[] = $date->format('j日');
            $bmis[] = $this->calcBmi($weight, $height);
        }

        return [
            'labels' => $labels, // 例：['10月14日', ...]
            'days' => $days,    // 例：['14日', ...]
            'bmis' => $bmis,    // 各日のBMI
            'average' => $this->calcAverage($bmis), // 平均BMI
        ];
    }

    // 直近1ヶ月（4週間）のBMIデータを取得
    private function getMonthData(int $userId, float $height, Carbon $today)
    {
        // 28日前（4週間前）から今日まで
        $periodStart = $today->copy()->subDays(self::DAYS_IN_MONTH - 1);

        // 対象期間のデータを取得
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$periodStart, $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $labels = $days = $bmis = [];

        // 各週ごとにBMIをまとめる
        for ($i = 0; $i < self::WEEKS_IN_MONTH; $i++) {
            $start = $periodStart->copy()->addDays($i * self::DAYS_IN_WEEK);
            $end = $start->copy()->addDays(self::DAYS_IN_WEEK - 1);

            $weekBmis = [];

            // 各日のBMIを集計
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->format('Y-m-d');
                if (isset($records[$key])) {
                    $weekBmis[] = $this->calcBmi($records[$key]->weight, $height);
                }
            }

            // 表示用
            $labels[] = $start->format('n/j') . '～' . $end->format('n/j');
            $days[] = $start->format('n/j');
            $bmis[] = $this->calcAverage($weekBmis); // 週平均
        }

        $monthAverage = $this->calcAverage($bmis); // 月平均
        $fullPeriodLabel = $periodStart->format('n月j日') . '～' . $end->format('n月j日');

        return [
            'labels' => $labels,               // 各週の範囲（例：10/1～10/7）
            'days' => $days,                   // 各週の開始日（例：10/1）
            'bmis' => $bmis,                   // 各週の平均BMI
            'average' => $monthAverage,        // 月全体の平均BMI
            'fullPeriodLabel' => $fullPeriodLabel, // 全期間の表示用
        ];
    }

    // 直近1年間のBMIデータを取得
    private function getYearData(int $userId, float $height, Carbon $today)
    {
        // 1年前の月初から今月末まで
        $start = $today->copy()->subMonths(self::MONTHS_IN_YEAR - 1)->startOfMonth();
        $end = $today->copy()->endOfMonth();

        // 各月のデータをまとめて取得・グループ化
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        $labels = $days = $bmis = [];

        // 12ヶ月分ループ
        for ($i = 0; $i < self::MONTHS_IN_YEAR; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('Y年n月'); // 表示用（例：2025年10月）
            $days[] = $month->format('n月');      // 月のみ（例：10月）

            if (isset($records[$key])) {
                // 該当月の全ての体重からBMIを算出
                $weights = $records[$key]->pluck('weight')->toArray();
                $monthBmis = array_map(fn($w) => $this->calcBmi($w, $height), $weights);
                $bmis[] = $this->calcAverage($monthBmis);
            } else {
                $bmis[] = null;
            }
        }

        return [
            'labels' => $labels,             // 例：['2025年1月', ...]
            'days' => $days,                 // 例：['1月', ...]
            'bmis' => $bmis,                 // 各月の平均BMI
            'average' => $this->calcAverage($bmis), // 年平均BMI
        ];
    }

    // BMIを計算する
    // weight[kg] / (height[m] × height[m])
    private function calcBmi(?float $weight, float $height): ?float
    {
        return $weight ? round($weight / ($height * $height), 2) : null;
    }

    // 平均値を計算する（nullを除外）
    private function calcAverage(array $values): ?float
    {
        $filtered = array_filter($values, fn($v) => $v !== null);
        return !empty($filtered) ? round(array_sum($filtered) / count($filtered), 2) : null;
    }
}
