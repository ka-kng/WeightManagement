<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HomeWeightController extends Controller
{
    public function weightShow(Request $request)
    {
        $userId = Auth::id();
        $period = $request->get('period', 'week');

        $today = Carbon::today();

        // 週
        // 上部表示用ラベル（フル日付）
        $weekLabels = [];
        // グラフ横軸用（日だけ）
        $weekDays = [];
        // DBから取得した体重配列
        $weekWeights = [];

        // DB取得
        $weekRecords = Record::where('user_id', $userId)
            ->whereBetween('date', [$today->copy()->subDays(6), $today])
            ->orderBy('date')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d'); // 日付だけをキー
            });

        for ($date = $today->copy()->subDays(6); $date->lte($today); $date->addDay()) {
            $weekLabels[] = $date->format('m月d日'); // 上部見出し用
            $weekDays[] = $date->format('j日');        // 横軸用
            $key = $date->format('Y-m-d');
            $weekWeights[] = $weekRecords[$key]->weight ?? null;
        }

        // 月
        $monthLabels = [];
        for ($date = $today->copy()->subDays(29); $date->lte($today); $date->addDay()) {
            $monthLabels[] = $date->format('m月d日');
        }


        // 年
        $yearLabels = [];
        for ($date = $today->copy()->subYear()->addDay(); $date->lte($today); $date->addDay()) {
            $yearLabels[] = $date->format('Y年m月d日');
        }

        return view('home.weight.show', compact(
            'weekLabels',
            'weekWeights',
            'weekDays',
            'monthLabels',
            'yearLabels'
        ));
    }
}
