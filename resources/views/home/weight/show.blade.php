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
            <a class="absolute left-0 pl-2 cursor-pointer text-2xl ">←</a>

            <h1 class="text-lg text-center">体重</h1>
        </div>

        <div x-data="{ tab: 'week' }" class="max-w-screen-lg mx-auto mt-6">

            <!-- タブ -->
            <div class="grid grid-cols-3  border-b border-gray-300">
                <button
                    :class="tab === 'week' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600'"
                    class="px-4 py-2 font-medium border-b-2 focus:outline-none" @click="tab = 'week'">週</button>

                <button
                    :class="tab === 'month' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600'"
                    class="px-4 py-2 font-medium border-b-2 focus:outline-none" @click="tab = 'month'">月</button>

                <button
                    :class="tab === 'year' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600'"
                    class="px-4 py-2 font-medium border-b-2 focus:outline-none" @click="tab = 'year'">年</button>
            </div>

            <!-- タブ内容 -->
            <div class="mt-6">
                <div x-show="tab === 'week'">
                    <!-- 週グラフ -->
                    <p class="text-center">{{ $weekLabels[0] }} ～ {{ end($weekLabels) }}</p>
                    <canvas id="weight-week-chart" data-labels='@json($weekDays)'
                        data-data='@json($weekWeights)' height="250" class="w-full mt-5 mx-auto"></canvas>
                </div>

                <div x-show="tab === 'month'" x-cloak>
                    <!-- 月グラフ -->
                    <p class="text-center">{{ $monthLabels[0] }} ～ {{ end($monthLabels) }}</p>
                    <canvas id="weight-month-chart"></canvas>
                </div>

                <div x-show="tab === 'year'" x-cloak>
                    <!-- 年グラフ -->
                    <p class="text-center">{{ $yearLabels[0] }} ～ {{ end($yearLabels) }}</p>
                    <canvas id="weight-year-chart"></canvas>
                </div>
            </div>

        </div>

    </div>

</body>

</html>
