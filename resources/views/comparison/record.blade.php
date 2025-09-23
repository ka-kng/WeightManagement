@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">1週間の記録比較</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-2 py-2">日付</th>
                    <th class="px-2 py-2">体重</th>
                    <th class="px-2 py-2">睡眠時間</th>
                    <th class="px-2 py-2">差分</th>
                    <th class="px-2 py-2">判定</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recordsWithPrevious as $i => $row)
                    @php
                        $current = $row['current'];
                        $previous = $row['previous'];

                        // 体重差分
                        $weightDiff = $previous ? round($current->weight - $previous->weight, 1) : null;
                        $weightJudge = $weightDiff !== null
                            ? ($weightDiff > 0 ? '増加' : ($weightDiff < 0 ? '減少' : '変化なし'))
                            : '-';
                        $weightDiffText = $weightDiff !== null
                            ? ($weightDiff > 0 ? '+'.$weightDiff : $weightDiff)
                            : '-';

                        // 睡眠差分（分単位）
                        if($previous){
                            $currentMinutes = $current->sleep_hours * 60 + $current->sleep_minutes;
                            $previousMinutes = $previous->sleep_hours * 60 + $previous->sleep_minutes;
                            $sleepDiffMinutes = $currentMinutes - $previousMinutes;
                            $sleepDiffHours = intdiv(abs($sleepDiffMinutes), 60);
                            $sleepDiffMins = abs($sleepDiffMinutes) % 60;
                            $sleepJudge = $sleepDiffMinutes > 0 ? '多い' : ($sleepDiffMinutes < 0 ? '少ない' : '普通');
                            $sleepDiffText = ($sleepDiffMinutes > 0 ? '+' : ($sleepDiffMinutes < 0 ? '-' : ''))
                                . "{$sleepDiffHours}時間{$sleepDiffMins}分";
                        } else {
                            $sleepDiffText = '-';
                            $sleepJudge = '-';
                        }

                        // 最新日だけ背景色
                        $bgClass = $i === count($recordsWithPrevious)-1 ? 'bg-green-100' : '';
                    @endphp

                    <tr class="text-center {{ $bgClass }}">
                        <td class="border px-2 py-1 text-left">{{ $current->date->format('n/d') }}</td>
                        <td class="border px-2 py-1">{{ number_format($current->weight, 1) }} kg</td>
                        <td class="border px-2 py-1">{{ $current->sleep_hours }}時間{{ $current->sleep_minutes }}分</td>
                        <td class="border px-2 py-1">{{ $weightDiffText }}kg</td>
                        <td class="border px-2 py-1">{{ $weightJudge }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
