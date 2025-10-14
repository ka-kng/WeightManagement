<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード再設定</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen flex items-center justify-center px-2">
    <div class="w-full max-w-md p-6 bg-white rounded shadow max-w-xl">
        <h2 class="text-2xl font-bold mb-4 text-center">パスワードをお忘れの方</h2>
        <p class="text-gray-600 mb-4 text-sm text-center">
            登録済みのメールアドレスを入力してください。<br>
            パスワード再設定用リンクを送信します。
        </p>

        @if (session('status'))
            <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium mb-1">メールアドレス</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autofocus
                    class="w-full border p-2 rounded">
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                リセットリンクを送信
            </button>

            <a href="{{ route('login') }}" class="block mt-4 text-blue-500 hover:underline text-center">
                ログイン画面に戻る
            </a>
        </form>
    </div>
</body>
</html>
