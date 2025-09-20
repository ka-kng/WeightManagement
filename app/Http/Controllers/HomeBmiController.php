<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeBmiController extends Controller
{

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

    private function getWeekData($userId, $height, Carbon $today)
    {
        $labels = [];
        $days = [];
        $bmis = [];

        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$today->copy()->subDays(6), $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        for ($date = $today->copy()->subDays(6); $date->lte($today); $date->addDay()) {
            $labels[] = $date->format('n月j日');
            $days[] = $date->format('j日');
            $key = $date->format('Y-m-d');

            $weight = $records[$key]->weight ?? null;
            $bmis[] = $weight ? round($weight / ($height * $height), 2) : null;
        }

        $average = !empty(array_filter($bmis)) ? round(array_sum(array_filter($bmis)) / count(array_filter($bmis)), 2) : null;

        return [$labels, $days, $bmis, $average];
    }

    private function getMonthData($userId, $height, Carbon $today)
    {
        $labels = [];
        $days = [];
        $bmis = [];

        $periodStart = $today->copy()->subDays(27);
        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$periodStart, $today])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        for ($i = 0; $i < 4; $i++) {
            $start = $today->copy()->subDays(27)->addDays($i * 7);
            $end = $start->copy()->addDays(6);

            $weekBmis = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->format('Y-m-d');
                if (isset($records[$key])) {
                    $weight = $records[$key]->weight;
                    $weekBmis[] = round($weight / ($height * $height), 1);
                }
            }

            $labels[] = $start->format('n/j') . '～' . $end->format('n/j');
            $bmis[] = !empty($weekBmis) ? round(array_sum($weekBmis) / count($weekBmis), 2) : null;
        }

        $monthAverage = !empty(array_filter($bmis)) ? round(array_sum(array_filter($bmis)) / count(array_filter($bmis)), 2) : null;

        $fullPeriodLabel = $periodStart->format('n月j日') . '～' . $end->format('n月j日');

        return [$labels, $days, $bmis, $monthAverage, $fullPeriodLabel];
    }

    private function getYearData($userId, $height, Carbon $today)
    {
        $labels = [];
        $days = [];
        $bmis = [];

        $records = Record::where('user_id', $userId)
            ->whereBetween('date', [$today->copy()->subYear()->addDay(), $today])
            ->get()
            ->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));

        foreach (range(0, 11) as $i) {
            $month = $today->copy()->subMonths(11 - $i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('Y年n月');
            $days[] = $month->format('n月');

            if (isset($records[$key])) {
                $weights = $records[$key]->pluck('weight')->toArray();
                $monthBmis = array_map(fn($w) => round($w / ($height * $height), 1), $weights);
                $bmis[] = round(array_sum($monthBmis) / count($monthBmis), 2);
            } else {
                $bmis[] = null;
            }
        }

        $yearAverage = !empty(array_filter($bmis)) ? round(array_sum(array_filter($bmis)) / count(array_filter($bmis)), 2) : null;

        return [$labels, $days, $bmis, $yearAverage];
    }
}
