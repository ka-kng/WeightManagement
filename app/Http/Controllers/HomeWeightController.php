<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HomeWeightController extends Controller
{
    public function show()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // 週
        [$weekLabels, $weekDays, $weekWeights] = $this->getWeekData($userId, $today);

        // 月（4週間ごとの平均を取得）
        [$monthLabels, $monthDays, $monthWeights, $fullPeriodLabel] = $this->getMonthData($userId, $today);

        // 年（12か月ごとの平均）
        [$yearLabels, $yearDays, $yearWeights] = $this->getYearData($userId, $today);

        return view('home.weight.show', compact(
            'weekLabels', 'weekDays', 'weekWeights',
            'monthLabels', 'monthDays', 'monthWeights', 'fullPeriodLabel',
            'yearLabels', 'yearDays', 'yearWeights'
        ));
    }

    private function getWeekData($userId, Carbon $today)
    {
        $weekLabels = [];
        $weekDays = [];
        $weekWeights = [];

        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$today->copy()->subDays(6), $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        for ($date = $today->copy()->subDays(6); $date->lte($today); $date->addDay()) {
            $weekLabels[] = $date->format('m月d日'); // 上部表示
            $weekDays[] = $date->format('j日');      // x軸用
            $key = $date->format('Y-m-d');
            $weekWeights[] = $records[$key]->weight ?? null;
        }

        return [$weekLabels, $weekDays, $weekWeights];
    }

    private function getMonthData($userId, Carbon $today)
    {
        $monthLabels = [];
        $monthDays = [];
        $monthWeights = [];

        $periodStart = $today->copy()->subDays(27); // 過去28日
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$periodStart, $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        for ($i = 0; $i < 4; $i++) {
            $start = $today->copy()->subDays(27)->addDays($i * 7);
            $end = $start->copy()->addDays(6);

            $weekWeights = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->format('Y-m-d');
                if (isset($records[$key])) {
                    $weekWeights[] = $records[$key]->weight;
                }
            }

            $monthLabels[] = $start->format('m/d') . '～' . $end->format('m/d');
            $monthDays[] = ($i + 1) . '週';
            $monthWeights[] = !empty($weekWeights) ? round(array_sum($weekWeights) / count($weekWeights), 1) : null;
        }

        $fullPeriodLabel = $periodStart->format('m月d日') . ' ～ ' . $end->format('m月d日');

        return [$monthLabels, $monthDays, $monthWeights, $fullPeriodLabel];
    }

    private function getYearData($userId, Carbon $today)
    {
        $yearLabels = [];
        $yearDays = [];
        $yearWeights = [];

        $start = $today->copy()->subMonths(11)->startOfMonth(); // 過去12か月
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$start, $today])
            ->get()
            ->groupBy(fn($r) => $r->date->format('Y-m')); // 月でグループ化

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $yearLabels[] = $month->format('Y年m月');
            $yearDays[] = $month->format('n月');

            $monthRecords = $records[$month->format('m')] ?? collect();
            $avg = $monthRecords->avg('weight');
            $yearWeights[] = $avg ? round($avg, 1) : null;
        }

        return [$yearLabels, $yearDays, $yearWeights];
    }
}
