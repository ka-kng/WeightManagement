@extends('layouts.app')

@section('content')
    <div class="max-w-screen-lg mx-auto px-6 mt-3">
        <div>
            @if ($record->exists)
                <a href="{{ route('records.show', $record->id) }}" class="hover:text-blue-500 text-xl underline">戻る</a>
            @else
                <a href="{{ route('records.index') }}" class="hover:text-blue-500 text-xl underline">戻る</a>
            @endif
        </div>
        <h1 class=" text-2xl font-bold mb-4 mt-5">データを{{ $record->exists ? '編集' : '新規登録' }}する</h1>

        <form method="POST" action="{{ $record->exists ? route('records.update', $record) : route('records.store') }}"
            enctype="multipart/form-data" class="space-y-5 mt-10">
            @csrf
            @if ($record->exists)
                @method('PUT')
            @endif
            <div>
                <div class="flex items-center justify-between">
                    <label for="">日付</label>
                    <input class="rounded-full" type="date" name="date"
                        value="{{ old('date', $record->date?->format('Y-m-d')) }}">
                </div>
                @error('date')
                    <p class="text-red-500 text-sm text-right mt-0">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label for="">体重</label>
                    <div>
                        <input class="w-20 rounded-full" name="weight" type="number" min="0" step="0.1"
                            value="{{ old('weight', $record->weight !== null ? number_format($record->weight, 1) : '') }}">
                        <span>kg</span>
                    </div>
                </div>
                @error('weight')
                    <p class="text-red-500 text-sm text-right mt-0">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label for="">睡眠時間</label>
                    <div>
                        <input name="sleep_hours" class="w-16 rounded-full" type="number" min="0" max="24"
                            step="1" value="{{ old('sleep_hours', $record->sleep_hours) }}"
                            oninput="this.value = Math.min(23, Math.max(0, this.value))">
                        <span>時間</span>
                        <input name="sleep_minutes" class="w-16 rounded-full" type="number" min="0" max="59"
                            step="1" value="{{ old('sleep_minutes', $record->sleep_minutes) }}"
                            oninput="this.value = Math.min(59, Math.max(0, this.value))">
                        <span>分</span>
                    </div>
                </div>
                @error('sleep_hours')
                    <p class="text-red-500 text-sm text-right mt-0">{{ $message }}</p>
                @enderror
                @error('sleep_minutes')
                    <p class="text-red-500 text-sm text-right mt-0">{{ $message }}</p>
                @enderror
            </div>

            <div class="">
                <label for="">食事</label>
                <div class="grid grid-cols-3 mt-3">

                    @php
                        $meals = ['炭水化物', 'タンパク質', '脂質', 'ビタミン', 'ミネラル'];
                        $selectedMeals = old('meals', $record->meals ? json_decode($record->meals, true) : []);
                    @endphp

                    @foreach ($meals as $meal)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="meals[]" value="{{ $meal }}"
                                class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500"
                                {{ in_array($meal, $selectedMeals) ? 'checked' : '' }}>
                            <span>{{ $meal }}</span>
                        </label>
                    @endforeach
                </div>

                <div>
                    <textarea class="w-full mt-3 rounded text-left" rows="3" name="meal_detail" id=""
                        placeholder="具体的な品名や、カロリーを入力することで、AI分析がより正確になります。">{{ old('meal_detail', $record->meal_detail) }}</textarea>
                </div>

                <div>

                    @if ($record->exists)
                        <label>食事写真を再アップロード(最大5枚まで)</label>
                    @else
                        <label>食事写真(最大5枚まで)</label>
                    @endif

                    <input type="file" name="meal_photos[]" id="meal_photos" accept="image/*" multiple
                        class="block text-sm text-gray-600 border rounded-lg p-2 cursor-pointer
                    file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100">

                    @error('meal_photos')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                    @error('meal_photos.*')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="">
                <label for="">運動</label>
                <div class="grid grid-cols-3 mt-3">

                    @php
                        $exercises = ['有酸素運動', '筋トレ', 'ストレッチ', 'ヨガ', 'スポーツ'];
                        $selectedExercises = old(
                            'exercises',
                            $record->exercises ? json_decode($record->exercises, true) : [],
                        );
                    @endphp

                    @foreach ($exercises as $exercise)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="exercises[]" value="{{ $exercise }}"
                                class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500"
                                {{ in_array($exercise, $selectedExercises) ? 'checked' : '' }}>
                            <span>{{ $exercise }}</span>
                        </label>
                    @endforeach

                </div>

                <div>
                    <textarea class="w-full mt-3 rounded text-left" rows="3" name="exercise_detail" id=""
                        placeholder="具体的にどんな運動をしたか、入力することで、AI分析がより正確になります。">{{ old('exercise_detail', $record->exercise_detail) }}</textarea>
                </div>
            </div>

            <div class="text-center">
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    保存する
                </button>
            </div>

        </form>

    </div>
@endsection
