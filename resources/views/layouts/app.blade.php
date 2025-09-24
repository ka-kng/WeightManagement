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
    <header class="p-6 flex items-center justify-between w-full bg-blue-500 text-white">
        <div>
            <a href="{{ route('home.index') }}" class="text-lg text-lg font-bold">体重管理アプリ</a>
        </div>
        <div>
            <ul class="flex items-center justify-between gap-5">
                <li class="hidden lg:block text-lg font-bold">
                    <a href="{{ route('comparison.latest') }}">
                        比較
                    </a>
                </li>
                <li class="hidden lg:block text-lg font-bold">
                    <a href="{{ route('records.index') }}">
                        記録
                    </a>
                </li>
                <li class="hidden lg:block text-lg font-bold">
                    <a href="{{ route('mypage.index') }}">
                        マイページ
                    </a>
                </li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-lg font-bold">
                        ログアウト
                    </button>
                </form>
            </ul>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 lg:hidden">
        <div class="flex justify-around text-center  divide-x divide-gray-300">
            <a href="{{ route('home.index') }}" class="flex-1 text-sm text-gray-700 hover:text-blue-600 py-8">
                ホーム
            </a>
            <a href="{{ route('comparison.latest') }}" class="flex-1 text-sm text-gray-700 hover:text-blue-600 py-8">
                比較
            </a>
            <a href="{{ route('records.index') }}" class="flex-1 text-sm text-gray-700 hover:text-blue-600 py-8">
                記録
            </a>
            <a href="{{ route('mypage.index') }}" class="flex-1 text-sm text-gray-700 hover:text-blue-600 py-8">
                マイページ
            </a>
        </div>
    </footer>

</body>

</html>
