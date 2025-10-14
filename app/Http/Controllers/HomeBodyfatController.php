<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeBodyfatController extends Controller
{
    private const GENDER_MALE = 1;

    // 体脂肪計算定数
    private const BODY_FAT_BASE = 3.02;
    private const BODY_FAT_WEIGHT_COEFF = 0.461;
    private const BODY_FAT_HEIGHT_COEFF = 0.089;
    private const BODY_FAT_AGE_COEFF = 0.038;
    private const BODY_FAT_CONST = -0.238;
    private const BODY_FAT_MALE_ADJUST = -6.85;

    private const DAYS_IN_WEEK = 7;
    private const WEEKS_IN_MONTH = 4;
    private const MONTHS_IN_YEAR = 12;

    public function show()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 年間データを一括取得してキャッシュ
        $records = $this->getRecordsBetween(
            $user->id,
            $today->copy()->subYear()->addDay(),
            $today
        );

        $weekData = $this->getPeriodData($user, $today->copy()->subDays(self::DAYS_IN_WEEK - 1), $today);
        $monthData = $this->getMonthlyData($user, $records, $today);
        $yearData = $this->getYearlyData($user, $records, $today);

        return view('home.chart.bodyfat', array_merge($weekData, $monthData, $yearData));
    }

    // 期間データ取得（任意開始日～終了日）
    private function getPeriodData($user, Carbon $start, Carbon $end): array
    {
        $records = $this->getRecordsBetween($user->id, $start, $end);

        $labels = $days = $bodyFat = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $labels[] = $date->format('n月d日');
            $days[] = $date->format('j日');

            $record = $records[$date->format('Y-m-d')] ?? null;
            $bodyFat[] = $this->calcBodyFatFromRecord($record, $user);
        }

        return [
            'weekLabels' => $labels,
            'weekDays' => $days,
            'weekBodyFat' => $bodyFat,
            'weekAverage' => $this->calcAverage($bodyFat),
        ];
    }

    // 月間データ取得（4週間）
    private function getMonthlyData($user, $records, Carbon $today): array
    {
        $periodStart = $today->copy()->subDays(self::DAYS_IN_WEEK * self::WEEKS_IN_MONTH - 1);

        $labels = $monthBodyFat = $weeksBodyFat = [];

        for ($i = 0; $i < self::WEEKS_IN_MONTH; $i++) {
            $start = $periodStart->copy()->addDays($i * self::DAYS_IN_WEEK);
            $end = $start->copy()->addDays(self::DAYS_IN_WEEK - 1);

            $weekValues = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $record = $records[$d->format('Y-m-d')] ?? null;
                $weekValues[] = $this->calcBodyFatFromRecord($record, $user);
            }

            $weeksBodyFat[] = $weekValues;
            $labels[] = $start->format('n/j') . '～' . $end->format('n/j');
            $monthBodyFat[] = $this->calcAverage($weekValues);
        }

        return [
            'monthLabels' => $labels,
            'monthDaysBodyFat' => $weeksBodyFat,
            'monthBodyFat' => $monthBodyFat,
            'monthAverage' => $this->calcAverage($monthBodyFat),
            'fullPeriodLabel' => $periodStart->format('n月j日') . '～' . $today->format('n月j日'),
        ];
    }

    // 年間データ取得（12ヶ月）
    private function getYearlyData($user, $records, Carbon $today): array
    {
        $start = $today->copy()->subMonths(self::MONTHS_IN_YEAR - 1)->startOfMonth();
        $labels = $months = $bodyFat = [];

        // 月単位にグループ化済み
        $recordsByMonth = $records->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        for ($i = 0; $i < self::MONTHS_IN_YEAR; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('Y年n月');
            $months[] = $month->format('n月');

            if (isset($recordsByMonth[$key])) {
                $values = array_map(fn($r) => $this->calcBodyFatFromRecord($r, $user), $recordsByMonth[$key]->toArray());
                $bodyFat[] = $this->calcAverage($values);
            } else {
                $bodyFat[] = null;
            }
        }

        return [
            'yearLabels' => $labels,
            'yearMonths' => $months,
            'yearBodyFat' => $bodyFat,
            'yearAverage' => $this->calcAverage($bodyFat),
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

    // レコードから体脂肪率計算
    private function calcBodyFatFromRecord($record, $user): ?float
    {
        if (!$record) return null;
        $age = $user->birth_date->age;
        return round($this->calcBodyFat($record['weight'], $user->height, $age, $user->gender), 1);
    }

    // 体脂肪率計算
    private function calcBodyFat(float $weight, float $height, int $age, int $gender): float
    {
        $val = self::BODY_FAT_BASE
            + self::BODY_FAT_WEIGHT_COEFF * $weight
            - self::BODY_FAT_HEIGHT_COEFF * $height
            + self::BODY_FAT_AGE_COEFF * $age
            + self::BODY_FAT_CONST;

        if ($gender === self::GENDER_MALE) {
            $val += self::BODY_FAT_MALE_ADJUST;
        }

        return ($val / $weight) * 100;
    }

    // 平均計算（nullを除外）
    private function calcAverage(array $values): ?float
    {
        $filtered = array_filter($values, fn($v) => $v !== null);
        return !empty($filtered) ? round(array_sum($filtered) / count($filtered), 1) : null;
    }
}
