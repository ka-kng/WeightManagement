<?php

namespace App\Services;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HomeBodyfatService
{
    // 性別定数
    private const GENDER_MALE = 1;

    // 体脂肪率計算に使う定数
    private const BODY_FAT_BASE = 3.02;          // 基本値
    private const BODY_FAT_WEIGHT_COEFF = 0.461; // 体重係数
    private const BODY_FAT_HEIGHT_COEFF = 0.089; // 身長係数
    private const BODY_FAT_AGE_COEFF = 0.038;    // 年齢係数
    private const BODY_FAT_CONST = -0.238;       // 定数補正
    private const BODY_FAT_MALE_ADJUST = -6.85;  // 男性補正

    // 日数・週・月数の定数
    private const DAYS_IN_WEEK = 7;
    private const WEEKS_IN_MONTH = 4;
    private const MONTHS_IN_YEAR = 12;

    // 体脂肪率データを「週・月・年」単位でまとめて取得
    // キャッシュ（6時間）を使ってパフォーマンスを向上
    public function getBodyFatData($user)
    {
        // 今日の日付を取得
        $today = Carbon::today();

        // キャッシュのキーを作成
        $cacheKey = "bodyfat_data_user_{$user->id}";

        // キャッシュにデータがあればそれを返す
        // なければクロージャ内の処理を実行してキャッシュに保存（6時間有効）
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($user, $today) {
            $records = $this->getRecordsBetween(
                $user->id,
                $today->copy()->subYear()->addDay(),
                $today
            );

            return [
                'week' => $this->getWeeklyData($user, $records, $today),
                'month' => $this->getMonthlyData($user, $records, $today),
                'year' => $this->getYearlyData($user, $records, $today),
            ];
        });
    }

    // 週のデータ取得
    private function getWeeklyData($user, $records, Carbon $today): array
    {
        $start = $today->copy()->subDays(self::DAYS_IN_WEEK - 1); // 7日前から今日まで
        $labels = [];    // グラフ上部用ラベル
        $days = [];      // x軸用ラベル
        $bodyFat = [];   // 体脂肪率の配列

        for ($date = $start->copy(); $date->lte($today); $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');

            $labels[] = $date->format('n月d日'); // 例：10月14日
            $days[] = $date->format('j日');      // 例：14日

            // レコードがある場合は体脂肪率を計算、ない場合はnull
            $record = $records[$formattedDate] ?? null;
            $bodyFat[] = $this->calcBodyFatFromRecord($record, $user);
        }

        return [
            'weekLabels' => $labels,
            'weekDays' => $days,
            'weekBodyFat' => $bodyFat,
            'weekAverage' => $this->calcAverage($bodyFat), // 週平均体脂肪率
        ];
    }

    // 直近1ヶ月（4週間）の体脂肪率データを取得
    private function getMonthlyData($user, $records, Carbon $today): array
    {
        // 直近4週間分を集計対象にする
        $periodStart = $today->copy()->subDays(self::DAYS_IN_WEEK * self::WEEKS_IN_MONTH - 1);
        $labels = [];        // 各週のラベル
        $weeksBodyFat = [];  // 日ごとの体脂肪率配列を格納
        $monthBodyFat = [];  // 週ごとの平均体脂肪率

        // 各週ごとに処理（4週分）
        for ($i = 0; $i < self::WEEKS_IN_MONTH; $i++) {
            $start = $periodStart->copy()->addDays($i * self::DAYS_IN_WEEK);
            $end = $start->copy()->addDays(self::DAYS_IN_WEEK - 1);

            $weekValues = []; // 1週間分の体脂肪率

            // 週の日ごとに体脂肪率を計算
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $record = $records[$d->format('Y-m-d')] ?? null;
                $weekValues[] = $this->calcBodyFatFromRecord($record, $user);
            }

            $weeksBodyFat[] = $weekValues;                         // 日別体脂肪率
            $labels[] = $start->format('n/j') . '～' . $end->format('n/j'); // 週ラベル
            $monthBodyFat[] = $this->calcAverage($weekValues);    // 週平均
        }

        return [
            'monthLabels' => $labels,
            'monthDaysBodyFat' => $weeksBodyFat,
            'monthBodyFat' => $monthBodyFat,
            'monthAverage' => $this->calcAverage($monthBodyFat), // 月平均
            'fullPeriodLabel' => $periodStart->format('n月j日') . '～' . $today->format('n月j日'),
        ];
    }

    // 直近1年間（12か月）の体脂肪率データを取得
    private function getYearlyData($user, $records, Carbon $today): array
    {
        $start = $today->copy()->subMonths(self::MONTHS_IN_YEAR - 1)->startOfMonth();
        $labels = [];    // グラフ用ラベル（例：2025年10月）
        $months = [];    // x軸用ラベル（例：10月）
        $bodyFat = [];   // 各月の平均体脂肪率

        // 月単位にグループ化（"2025-10" のようなキーでまとめる）
        $recordsByMonth = $records->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        // 各月ごとに処理
        for ($i = 0; $i < self::MONTHS_IN_YEAR; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('Y年n月'); // グラフ上部ラベル
            $months[] = $month->format('n月');    // x軸ラベル

            if (isset($recordsByMonth[$key])) {
                $values = array_map(
                    fn($r) => $this->calcBodyFatFromRecord($r, $user),
                    $recordsByMonth[$key]->toArray()
                );
                $bodyFat[] = $this->calcAverage($values); // 月平均
            } else {
                $bodyFat[] = null; // データなし
            }
        }

        return [
            'yearLabels' => $labels,
            'yearMonths' => $months,
            'yearBodyFat' => $bodyFat,
            'yearAverage' => $this->calcAverage($bodyFat), // 年平均
        ];
    }

    // 日付をキーにした連想配列で返す
    private function getRecordsBetween(int $userId, Carbon $start, Carbon $end)
    {
        return Record::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));
    }

    // 1日のレコードから体脂肪率を算出
    private function calcBodyFatFromRecord($record, $user): ?float
    {
        if (!$record) return null;

        $age = $user->birth_date->age; // 年齢取得

        // BMIの計算と同様に体脂肪率を計算
        return round($this->calcBodyFat(
            $record['weight'],
            $user->height,
            $age,
            $user->gender
        ), 1);
    }

    // 体脂肪率計算式
    private function calcBodyFat(float $weight, float $height, int $age, int $gender): float
    {
        $val = self::BODY_FAT_BASE
            + self::BODY_FAT_WEIGHT_COEFF * $weight
            - self::BODY_FAT_HEIGHT_COEFF * $height
            + self::BODY_FAT_AGE_COEFF * $age
            + self::BODY_FAT_CONST;

        // 男性の場合は補正
        if ($gender === self::GENDER_MALE) {
            $val += self::BODY_FAT_MALE_ADJUST;
        }

        // 体脂肪率に変換
        return ($val / $weight) * 100;
    }

    // 配列の平均値を計算（nullは除外）
    private function calcAverage(array $values): ?float
    {
        $filtered = array_filter($values, fn($v) => $v !== null);
        return !empty($filtered) ? round(array_sum($filtered) / count($filtered), 1) : null;
    }
}
