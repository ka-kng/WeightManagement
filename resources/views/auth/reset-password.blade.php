<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード再設定</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6 bg-white rounded shadow max-w-xl">
        <h2 class="text-2xl font-bold mb-4 text-center">パスワード再設定</h2>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->token }}">

            <input type="hidden" name="email" value="{{ $request->email }}">

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium mb-1">新しいパスワード</label>
                <input id="password" type="password" name="password" class="w-full border p-2 rounded">
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium mb-1">パスワード確認</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                    class="w-full border p-2 rounded">
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                パスワードを再設定
            </button>

            <a href="{{ route('login') }}" class="block mt-4 text-blue-500 hover:underline text-center">
                ログイン画面に戻る
            </a>
        </form>
    </div>
</body>
</html>
