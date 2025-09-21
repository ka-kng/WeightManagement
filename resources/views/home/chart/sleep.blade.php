<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])



</head>

<body class=" pb-28">

    <div class="max-w-screen-lg mx-auto">
        <div class="relative flex items-center justify-center py-3 border-b border-gray-300 font-bold">
            <a href="{{ route('home.index') }}" class="absolute left-0 pl-2 cursor-pointer text-2xl ">←</a>

            <h1 class="text-lg text-center">体重</h1>
        </div>

        <div x-data="sleepTabs()" class="max-w-screen-lg mx-auto mt-6">

            <!-- タブ -->
            <div class="grid grid-cols-3 border-b border-gray-300">
                <button
                    :class="tab === 'week' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600'"
                    class="px-4 py-2 font-medium border-b-2 focus:outline-none" @click="changeTab('week')">週</button>

                <button
                    :class="tab === 'month' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600'"
                    class="px-4 py-2 font-medium border-b-2 focus:outline-none" @click="changeTab('month')">月</button>

                <button
                    :class="tab === 'year' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600'"
                    class="px-4 py-2 font-medium border-b-2 focus:outline-none" @click="changeTab('year')">年</button>
            </div>

            <!-- タブ内容 -->
            <div class="mt-6">
                <div x-show="tab === 'week'" x-cloak>
                    @if (!empty($weekLabels))
                        <p class="text-center">{{ $weekLabels[0] }} ～ {{ end($weekLabels) }}</p>
                    @else
                        <p class="text-center text-gray-400">データがありません</p>
                    @endif
                    <canvas id="sleep-week-chart" data-labels='@json($weekDays)'
                        data-data='@json($weekSleep)' height="250" class="w-full mt-5 mx-auto"></canvas>

                    <div class="px-5 mt-5">
                        <p class="font-bold text-xl">{{ $weekLabels[0] }} ～ {{ end($weekLabels) }}の平均体重</p>
                        <p class="mt-5 text-5xl text-right"> {{ is_null($weekAverage) ? '-' : floor($weekAverage).'時間'.round(($weekAverage - floor($weekAverage))*60).'分' }}</p>
                    </div>
                </div>

                <div x-show="tab === 'month'" x-cloak>
                    @if (!empty($monthLabels))
                        <p class="text-center">{{ $fullPeriodLabel }}</p>
                    @else
                        <p class="text-center text-gray-400">データがありません</p>
                    @endif
                    <canvas id="sleep-month-chart" data-labels='@json($monthLabels)'
                        data-data='@json($monthSleep)' height="250" class="w-full mt-5 mx-auto"></canvas>
                    <div class="px-5 mt-5">
                        <p class="font-bold text-xl">{{ $fullPeriodLabel }}の平均体重</p>
                        <p class="mt-5 text-5xl text-right">{{ is_null($monthAverage) ? '-' : floor($monthAverage).'時間'.round(($monthAverage - floor($monthAverage))*60).'分' }}</p>
                    </div>
                </div>

                <div x-show="tab === 'year'" x-cloak>
                    @if (!empty($yearLabels))
                        <p class="text-center">{{ $yearLabels[0] }} ～ {{ end($yearLabels) }}</p>
                    @else
                        <p class="text-center text-gray-400">データがありません</p>
                    @endif
                    <canvas id="sleep-year-chart" data-labels='@json($yearDays)'
                        data-data='@json($yearSleep)' height="250" class="w-full mt-5 mx-auto"></canvas>
                    <div class="px-5 mt-5">
                        <p class="font-bold text-xl">{{ $yearLabels[0] }} ～ {{ end($yearLabels) }}の平均体重</p>
                        <p class="mt-5 text-5xl text-right">{{ is_null($yearAverage) ? '-' : floor($yearAverage).'時間'.round(($yearAverage - floor($yearAverage))*60).'分' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
