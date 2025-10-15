@extends('layouts.app')

@section('content')
    <div class="max-w-screen-lg mx-auto px-6">
        <div class="flex justify-between items-center mt-3">
            <h1 class="text-xl font-bold">記録一覧</h1>
            <a class="text-blue-600" href="{{ route('records.create') }}">+記録する</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            @foreach ($records as $record)
                <a href="{{ route('records.show', $record->id) }}"
                    class="block rounded-2xl shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 p-4 border border-gray-200 space-y-3 h-full">

                    <div class="flex items-center justify-between border-b pb-2 mb-3">
                        <h2 class="text-xl font-semibold text-gray-800">
                            {{ $record->date->format('Y/m/d') }}
                        </h2>
                        <span class="text-sm text-gray-500">#記録</span>
                    </div>

                    <div class="">
                        <span class="font-medium">体重：{{ number_format($record->weight, 1) }} kg</span>
                    </div>

                    <div class="">
                        <span>睡眠時間：</span>
                        <span>{{ $record->sleep_hours }}時間{{ $record->sleep_minutes }}分</span>
                    </div>

                    <div class="">
                        <p>摂取内容：</p>
                        <p class="grid grid-cols-3 gap-3 mt-2">
                            @php
                                $meals = is_string($record->meals) ? json_decode($record->meals, true) : $record->meals;
                            @endphp

                            @if ($meals && is_array($meals))
                                @foreach ($meals as $meal)
                                    <span class="px-2 py-1 text-sm bg-blue-50 text-blue-700 rounded-full text-center">
                                        {{ $meal }}
                                    </span>
                                @endforeach
                            @else
                                <span>なし</span>
                            @endif
                        </p>
                    </div>

                    <div class="">
                        <p>運動内容：</p>
                        <p class="grid grid-cols-3 gap-3 mt-2">
                            @php
                                $exercises = is_string($record->exercises)
                                    ? json_decode($record->exercises, true)
                                    : $record->exercises;
                            @endphp

                            @if ($exercises && is_array($exercises))
                                @foreach ($exercises as $exercise)
                                    <span class="px-2 py-1 text-sm bg-blue-50 text-blue-700 rounded-full text-center">
                                        {{ is_array($exercise) ? implode(', ', $exercise) : $exercise }}
                                    </span>
                                @endforeach
                            @else
                                <span>なし</span>
                            @endif
                        </p>
                    </div>

                </a>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $records->links() }}
        </div>
    </div>
@endsection
