@extends('layouts.app')

@section('content')
    <div class="max-w-screen-lg mx-auto px-6">
        <div class="flex justify-between items-center mt-3">
            <h1 class="text-lg font-bold">ホーム（直近の記録）</h1>
        </div>
        @if ($record)
            <div class="flex items-center justify-between">
                <p class="mt-3">日付：{{ $record->date->format('Y/m/d') }}</p>
                <p class="mt-3">目標体重：{{ number_format($record->user->target_weight, 1) }} kg</p>
            </div>
        @else
            <p class="mt-3 text-gray-500">まだ記録がありません。</p>
        @endif


        <div class="grid grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

            <a href="{{ route('home.weight') }}"
                class="border border-black p-3 hover:bg-blue-500 hover:text-white hover:border-white rounded">
                @if ($record)
                    <div class="flex items-center justify-between">
                        <p>体重</p>
                        <p>→</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl">{{ number_format($record->weight, 1) }} kg</p>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <p>体重</p>
                        <p>→</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl text-gray-500">記録なし</p>
                    </div>
                @endif

            </a>

            <a href="{{ route('home.bmi') }}"
                class="border border-black p-3 hover:bg-blue-500 hover:text-white hover:border-white rounded">
                @if ($record)
                    <div class="flex items-center justify-between">
                        <p>BMI</p>
                        <p>→</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl">{{ number_format($record->bmi, 2) }}</p>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <p>BMI</p>
                        <p>→</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl text-gray-500">未計算</p>
                    </div>
                @endif
            </a>

            <a href="{{ route('home.bodyfat') }}"
                class="border border-black p-3 hover:bg-blue-500 hover:text-white hover:border-white rounded">
                @if ($record)
                    <div class="flex items-center justify-between">
                        <p>体脂肪率</p>
                        <p>→</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl">{{ number_format($record->body_fat, 1) }} %</p>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <p>体脂肪率</p>
                        <p>→</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl text-gray-500">未記録</p>
                    </div>
                @endif
            </a>

            <a href="{{ route('home.sleep') }}"
                class="border border-black p-3 hover:bg-blue-500 hover:text-white hover:border-white rounded">
                @if ($record)
                    <div class="flex items-center justify-between">
                        <p>睡眠時間</p>
                        <p>→</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl">
                            {{ $record->sleep_hours }}時間{{ $record->sleep_minutes }}分
                        </p>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <p>睡眠時間</p>
                        <p>→</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-3xl text-gray-500">未記録</p>
                    </div>
                @endif
            </a>

        </div>

    </div>
@endsection
