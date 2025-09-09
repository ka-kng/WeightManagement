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

<body class="px-6">
    <header class="max-w-screen-lg mx-auto py-6 flex items-center justify-between">
        <div>
            <a href="/">体重管理アプリ</a>
        </div>
        <div>
            <ul class="flex items-center justify-between gap-5">
                <li class="hidden lg:block">
                    <a href="">
                        AI分析
                    </a>
                </li>
                <li class="hidden lg:block">
                    <a href="{{ route('records.store') }}">
                        記録
                    </a>
                </li>
                <li class="hidden lg:block">
                    <a href="">
                        マイページ
                    </a>
                </li>
                <li>
                    ログアウト
                </li>
            </ul>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 lg:hidden">
        <div class="flex justify-around text-center  divide-x divide-gray-300">
            <a href="" class="flex-1 text-sm text-gray-700 hover:text-blue-600 py-8">
                ホーム
            </a>
            <a href="" class="flex-1 text-sm text-gray-700 hover:text-blue-600 py-8">
                AI分析
            </a>
            <a href="{{ route('records.store') }}" class="flex-1 text-sm text-gray-700 hover:text-blue-600 py-8">
                記録
            </a>
            <a href="" class="flex-1 text-sm text-gray-700 hover:text-blue-600 py-8">
                マイページ
            </a>
        </div>
    </footer>

</body>

</html>
