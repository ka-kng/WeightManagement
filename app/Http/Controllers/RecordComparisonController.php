<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use Illuminate\Support\Facades\Auth;

class RecordComparisonController extends Controller
{
    private const LATEST_COUNT = 10;

    public function latest()
    {
        $userId = Auth::id();

        // 最新10件取得（降順 → 昇順に整列）
        $records = $this->getLatestRecords($userId);

        // 差分・判定・最新日のフラグを付与
        $recordsWithPrevious = $this->prepareComparisonData($records);

        return view('comparison.record', compact('recordsWithPrevious'));
    }

    private function getLatestRecords(int $userId)
    {
        return Record::where('user_id', $userId)
            ->orderByDesc('date')
            ->take(self::LATEST_COUNT)
            ->get()
            ->sortBy('date')
            ->values();
    }

    private function prepareComparisonData($records)
    {
        return $records->map(function ($record, $index) use ($records) {
            $previous = $index > 0 ? $records[$index - 1] : null;

            // 体重差分と判定
            $weightDiff = $previous ? round(($record->weight ?? 0) - ($previous->weight ?? 0), 1) : null;
            $weightJudge = $weightDiff !== null
                ? ($weightDiff > 0 ? '増加' : ($weightDiff < 0 ? '減少' : '変化なし'))
                : '-';
            $weightDiffText = $weightDiff !== null
                ? ($weightDiff > 0 ? '+'.$weightDiff : $weightDiff)
                : '-';

            return [
                'current' => $record,
                'previous' => $previous,
                'weightDiffText' => $weightDiffText,
                'weightJudge' => $weightJudge,
                'isLatest' => $index === count($records) - 1,
            ];
        });
    }
}
