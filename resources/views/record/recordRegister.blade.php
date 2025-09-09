@extends('layouts.app') {{-- 今のテンプレート名が app.blade.php の場合 --}}

@section('content')
<div class="max-w-screen-lg mx-auto">
    <h1 class=" text-2xl font-bold mb-4">データを登録(編集)する</h1>

    <form class="space-y-3 mt-10" method="POST" action="{{ route('records.store') }}">
        @csrf
        <div class="flex items-center justify-between">
            <label for="">日付</label>
            <input
                class="rounded-full"
                type="date">
        </div>

        <div class="flex items-center justify-between">
            <label for="">体重</label>
            <div>
                <input class="w-20 rounded-full" type="number" min="0">
                <span>kg</span>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <label for="">睡眠時間</label>
            <div>
                <input
                    class="w-16 rounded-full"
                    type="number"
                    min="0"
                    max="24"
                    step="1"
                    oninput="this.value = Math.min(23, Math.max(0, this.value))">
                <span>時間</span>
                <input
                    class="w-16 rounded-full"
                    type="number"
                    min="0"
                    max="59"
                    step="1"
                    oninput="this.value = Math.min(59, Math.max(0, this.value))">
                <span>分</span>
            </div>
        </div>

        <div class="">
            <label for="">食事</label>
            <div class="grid grid-cols-3 mt-3">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>炭水化物</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>タンパク質</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>脂質</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>ビタミン</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>ミネラル</span>
                </label>
            </div>
        </div>

    </form>

</div>
@endsection
