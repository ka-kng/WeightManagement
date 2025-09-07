<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>新規登録</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen flex items-center justify-center">
  <div class="w-full max-w-xl mx-auto p-4 md:border rounded md:shadow">
    <h2 class="text-center text-3xl font-bold my-4 md:mt-2">新規登録</h2>

    <form class="p-4 border rounded shadow" method="POST" action="{{ route('register') }}">
      @csrf

      <div class="mb-4">
        <label for="name" class="block font-medium">名前</label>
        <input type="text" name="name" id="name" class="w-full border p-2 rounded" value="{{ old('name') }}">
        @error('name')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-4">
        <label for="email" class="block font-medium">メールアドレス</label>
        <input type="email" name="email" id="email" class="w-full border p-2 rounded" value="{{ old('email') }}">
        @error('email')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-4">
        <label for="password" class="block font-medium">パスワード</label>
        <input type="password" name="password" id="password" class="w-full border p-2 rounded">
        @error('password')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-4">
        <label for="password_confirmation" class="block font-medium">パスワード確認</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="w-full border p-2 rounded">
        @error('password_confirmation')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-4">
        <div class="flex justify-between">
          <label class="block font-medium">性別</label>
          <div>
            <label><input type="radio" name="gender" value="0" {{ old('gender') === '0' ? 'checked' : '' }}> 女性</label>
            <label class="ml-4"><input type="radio" name="gender" value="1" {{ old('gender') === '1' ? 'checked' : '' }}> 男性</label>
          </div>
        </div>
        @error('gender')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-4">
        <div class="flex items-center justify-between">
          <label for="birth_date" class="block font-medium">生年月日</label>
          <input type="date" name="birth_date" id="birth_date" class=" border p-2 rounded" value="{{ old('birth_date') }}">
        </div>
        @error('birth_date')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-4">
        <div class="flex items-center justify-between">
          <label for="height" class="block font-medium">身長 (cm)</label>
          <input type="number" step="0.1" name="height" id="height" class="border p-2 rounded" value="{{ old('height') }}" placeholder="例：160.5">
        </div>
        @error('height')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-4">
        <div class="flex items-center justify-between">
          <label for="target_weight" class="block font-medium">目標体重 (kg)</label>
          <input type="number" step="0.1" name="target_weight" id="target_weight" class="border p-2 rounded" value="{{ old('target_weight') }}" placeholder="例：50.5">
        </div>
        @error('target_weight')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mt-6">
        <button type="submit" class="w-full bg-green-500 text-white p-2 rounded hover:bg-green-600">登録</button>
      </div>
    </form>
  </div>
</body>

</html>
