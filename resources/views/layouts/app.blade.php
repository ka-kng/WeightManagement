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
        <header class="max-w-screen-xl mx-auto py-6 flex items-center justify-between">
            <div>
                <a href="/">体重管理アプリ</a>
            </div>
            <div>
                <ul class="flex items-center justify-between gap-5">
                    <li class="hidden lg:block">
                        ログアウト
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

    </body>
</html>
