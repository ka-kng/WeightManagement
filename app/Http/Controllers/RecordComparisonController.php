<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use Illuminate\Support\Facades\Auth;

class RecordComparisonController extends Controller
{
    // 最新7日分を比較表示
    public function latest()
    {
        $userId = Auth::id(); // ログイン中ユーザー

        // 最新7件のレコード取得（降順）
        $records = Record::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        if ($records->isEmpty()) {
            abort(404, '記録がありません');
        }

        // 前日との比較用に配列化して日付順に逆順（古い順）
        $records = $records->sortBy('date')->values();

        // 各レコードの前日データをセット
        $recordsWithPrevious = $records->map(function ($record, $index) use ($records) {
            $previous = $index > 0 ? $records[$index - 1] : null;
            return [
                'current' => $record,
                'previous' => $previous,
            ];
        });

        return view('comparison.record', compact('recordsWithPrevious'));
    }
}
