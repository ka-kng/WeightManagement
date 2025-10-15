@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-6">過去10日間の記録比較</h2>

        @if (isset($recordsWithPrevious) && count($recordsWithPrevious) > 0)
            <div>
                <table class="min-w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-2 py-2">日付</th>
                            <th class="px-2 py-2">体重</th>
                            <th class="px-2 py-2">睡眠時間</th>
                            <th class="px-2 py-2">体重差分</th>
                            <th class="px-2 py-2">判定</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recordsWithPrevious as $row)
                            @php
                                $bgClass = match ($row['weightJudge']) {
                                    '増加' => 'bg-red-100',
                                    '減少' => 'bg-blue-100',
                                    default => '',
                                };
                            @endphp

                            <tr class="text-center {{ $bgClass }}">
                                <td class="border px-2 py-2 text-left">
                                    {{ $row['current']->date?->format('n/d') ?? '-' }}
                                </td>
                                <td class="border px-2 py-2">
                                    {{ $row['current']->weight ? number_format($row['current']->weight, 1) . ' kg' : '-' }}
                                </td>
                                <td class="border px-2 py-2">
                                    @if ($row['current']->sleep_hours !== null || $row['current']->sleep_minutes !== null)
                                        {{ $row['current']->sleep_hours }}時間{{ $row['current']->sleep_minutes }}分
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="border px-2 py-2">{{ $row['weightDiffText'] }}kg</td>
                                <td class="border px-2 py-2">{{ $row['weightJudge'] }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-10 text-gray-500 border border-gray-300 rounded-lg">
                <p>記録がまだありません。</p>
                <p class="text-sm mt-2">最初の記録を登録すると10日間の比較が表示されます。</p>
            </div>
        @endif
    </div>
@endsection
