<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen flex items-center justify-center">
    <div class="w-full max-w-xl mx-auto p-4 md:border rounded md:shadow">
        <h1 class="text-center text-3xl font-bold mb-4">ログイン</h1>

        @if (session('status'))
            <div class="bg-green-100 p-2 mb-4">{{ session('status') }}</div>
        @endif

        <form class="p-4 border rounded shadow" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="block font-medium">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus
                    class="w-full border p-2 rounded">
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block font-medium">パスワード</label>
                <input id="password" type="password" name="password" class="w-full border p-2 rounded">
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4 flex items-center">
                <input type="checkbox" name="remember" id="remember" class="mr-2">
                <label for="remember" class="font-medium">ログイン状態を保持する</label>
            </div>

            @if (session('resent'))
                <div class="bg-green-100 p-2 mb-4 text-center text-green-700 text-center">
                    <p>確認メールを送信しました。<br>メールボックスをご確認のうえ、メール内のリンクをクリックして認証を完了してください。</p>
                </div>
            @endif

            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                ログイン
            </button>

            <a href="{{ route('register') }}" class="block mt-2 text-blue-500 hover:underline">
                登録はこちら
            </a>
            <a href="{{ route('password.request') }}" class="block mt-2 text-blue-500 hover:underline">
                パスワードをお忘れの方はこちら
            </a>
        </form>


    </div>
</body>

</html>
