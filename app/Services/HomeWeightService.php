<?php

namespace App\Services;

use App\Models\Record;
use Carbon\Carbon;

class HomeWeightService
{
  // 定数定義：1週間の日数・1ヶ月を4週間とした日数・1年の月数
  private const DAYS_IN_WEEK = 7;
  private const WEEKS_IN_MONTH = 4;
  private const DAYS_IN_MONTH = self::DAYS_IN_WEEK * self::WEEKS_IN_MONTH;
  private const MONTHS_IN_YEAR = 12;

  // 週・月・年の体重データをまとめて取得
  public function getWeightData(int $userId)
  {
    $today = Carbon::today();

    return [
      'week' => $this->getWeekData($userId, $today),   // 直近1週間分
      'month' => $this->getMonthData($userId, $today), // 直近4週間分
      'year' => $this->getYearData($userId, $today),   // 直近1年分
    ];
  }

  // 直近1週間の体重データを取得
  private function getWeekData(int $userId, Carbon $today)
  {
    // 7日分のデータを取得（今日含む）
    $records = Record::where('user_id', $userId)
      ->whereBetween('date', [$today->copy()->subDays(self::DAYS_IN_WEEK - 1), $today])
      ->get()
      ->keyBy(fn($r) => $r->date->format('Y-m-d')); // 日付をキーにする

    $labels = [];
    $days = [];
    $weights = [];

    // 各日のデータを作成
    for ($date = $today->copy()->subDays(self::DAYS_IN_WEEK - 1); $date->lte($today); $date->addDay()) {
      $key = $date->format('Y-m-d');
      $labels[] = $date->format('n月d日'); // 上部表示用
      $days[] = $date->format('j日');      // x軸用
      $weights[] = $records[$key]->weight ?? null;
    }

    return [
      'labels' => $labels,
      'days' => $days,
      'weights' => $weights,
      'average' => $this->calcAverage($weights), // 週平均体重
    ];
  }

  // 直近1ヶ月（4週間）の体重データを取得
  private function getMonthData(int $userId, Carbon $today)
  {
    $periodStart = $today->copy()->subDays(self::DAYS_IN_MONTH - 1);

    // 対象期間のデータを取得
    $records = Record::where('user_id', $userId)
      ->whereBetween('date', [$periodStart, $today])
      ->get()
      ->keyBy(fn($r) => $r->date->format('Y-m-d'));

    $labels = [];
    $weights = [];

    // 各週ごとの平均を計算
    for ($i = 0; $i < self::WEEKS_IN_MONTH; $i++) {
      $start = $periodStart->copy()->addDays($i * self::DAYS_IN_WEEK);
      $end = $start->copy()->addDays(self::DAYS_IN_WEEK - 1);

      $weekWeights = [];

      // 週の日ごとに体重を収集
      for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
        $key = $d->format('Y-m-d');
        if (isset($records[$key])) {
          $weekWeights[] = $records[$key]->weight;
        }
      }

      // 週ラベルと平均体重を追加
      $labels[] = $start->format('n/d') . '～' . $end->format('n/d'); // ラベル例: 10/1～10/7
      $weights[] = $this->calcAverage($weekWeights);                  // 週平均
    }

    // 月全体の表示ラベル（期間表示）
    $fullPeriodLabel = $periodStart->format('n月d日') . ' ～ ' . $end->format('n月d日');

    // 月平均体重
    $monthAverage = $this->calcAverage($weights);

    return [
      'labels' => $labels,               // 各週ラベル
      'weights' => $weights,             // 各週平均体重
      'fullPeriodLabel' => $fullPeriodLabel, // 月全体の期間表示
      'average' => $monthAverage,        // 月平均体重
    ];
  }

  // 直近1年間（12か月）の体重データを取得
  private function getYearData(int $userId, Carbon $today)
  {
    $start = $today->copy()->subMonths(self::MONTHS_IN_YEAR - 1)->startOfMonth();
    $end = $today->copy()->endOfMonth();

    $records = Record::where('user_id', $userId)
      ->whereBetween('date', [$start, $end])
      ->get()
      ->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

    $labels = [];
    $days = [];
    $weights = [];

    for ($i = 0; $i < self::MONTHS_IN_YEAR; $i++) {
      $month = $start->copy()->addMonths($i);
      $key = $month->format('Y-m');

      $labels[] = $month->format('Y年n月'); // 表示用
      $days[] = $month->format('n月');

      $monthWeights = isset($records[$key])
        ? $records[$key]->pluck('weight')->toArray()
        : [];

      $weights[] = $this->calcAverage($monthWeights); // 月平均
    }

    $yearAverage = $this->calcAverage($weights);

    return [
      'labels' => $labels,
      'days' => $days,
      'weights' => $weights,
      'average' => $yearAverage,
    ];
  }

  // 平均値を計算（nullを除外）
  private function calcAverage(array $values): ?float
  {
    $filtered = array_filter($values, fn($v) => $v !== null);
    return !empty($filtered)
      ? round(array_sum($filtered) / count($filtered), 1)
      : null;
  }
}
