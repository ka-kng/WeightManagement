@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-6">プロフィール</h2>

        <form action="{{ route('mypage.update') }}" class="mx-3 space-y-4" method="POST">
            @csrf
            @method('PUT')
            <div class="flex items-center justify-between">
                <label for="name">名前</label>
                <input id="name" type="text" value="{{ old('name', $user->name) }}" name="name" class="rounded">
            </div>
            <div class="flex items-center justify-between">
                <label for="birth_date">生年月日</label>
                <input class="rounded" type="date" name="birth_date" id="birth_date"
                    value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
            </div>
            <div class="flex items-center justify-between">
                <label for="height">身長</label>
                <div>
                    <input
                      class="rounded w-20"
                      type="number"
                      name="height"
                      id="height"
                      step="0.1"
                      value="{{ old('height', number_format($user->height, 1)) }}">
                    <span>cm</span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <label for="target_weight">目標の体重</label>
                <div>
                    <input
                      class="rounded w-20"
                      type="number"
                      name="target_weight"
                      id="target_weight"
                      step="0.1"
                      value="{{ old('target_weight', number_format($user->target_weight, 1)) }}">
                    <span>kg</span>
                </div>
            </div>
            <div class="flex justify-between">
                <label>性別</label>
                <div>
                    <label><input type="radio" name="gender" value="0"
                            {{ old('gender', $user->gender) == '0' ? 'checked' : '' }}>
                        女性</label>
                    <label class="ml-4"><input type="radio" name="gender" value="1"
                            {{ old('gender', $user->gender) == '1' ? 'checked' : '' }}> 男性</label>
                </div>
            </div>
            <div class="flex flex-col">
                <label for="email">メールアドレス</label>
                <input class="rounded mt-3" type="email" name="email" id="email"
                    value="{{ old('email', $user->email) }}">
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full bg-green-500 text-white p-2 rounded hover:bg-green-600">登録</button>
            </div>
        </form>

        <div>

        </div>

    </div>
@endsection
