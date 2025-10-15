@extends('layouts.app')

@section('content')
    <div class="max-w-screen-lg mx-auto px-6">
        <div class="flex justify-between items-center mt-3">

            <div>
                <a href="{{ route('records.index') }}" class="hover:text-blue-500 text-xl underline">戻る</a>
            </div>

            <div class="flex items-center gap-5 text-xl">
                <div x-data="{ open: false }" class="inline">
                    <button @click="open = true" class="text-red-600">削除する</button>

                    <!-- モーダル -->
                    <div x-show="open"
                        class="fixed px-3 inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-96">
                            <p class="mb-4 text-center text-gray-700">本当に削除しますか？</p>
                            <div class="flex justify-end space-x-2">
                                <button @click="open = false"
                                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">キャンセル</button>

                                <form action="{{ route('records.destroy', $record->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">削除</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <a class="text-blue-600" href="{{ route('records.edit', $record->id) }}">編集する</a>
            </div>

        </div>

        <h1 class="text-xl font-bold mt-5">記録詳細</h1>

        <div
            class="block rounded-2xl shadow-md hover:shadow-xl transform duration-300 p-4 border border-gray-200 h-full mt-5">
            <div class="space-y-3">
                <div>
                    <p class="font-medium text-lg">日付：{{ $record->date->format('Y/m/d') }}</p>
                </div>

                <div>
                    <p class="font-medium text-lg">体重：{{ number_format($record->weight, 1) }}kg</p>
                </div>

                <div>
                    <p class="font-medium text-lg">睡眠時間：{{ $record->sleep_hours }}時間{{ $record->sleep_minutes }}分</p>
                </div>

                <div>
                    <p class="font-medium text-lg">摂取内容：</p>
                    <p class="grid grid-cols-3 gap-3">
                        @php
                            $meals = is_string($record->meals) ? json_decode($record->meals, true) : $record->meals;
                        @endphp

                        @if (!empty($meals) && is_array($meals))
                            @foreach ($meals as $meal)
                                <span class="px-2 py-1 mt-2 text-sm bg-blue-50 text-blue-700 rounded-full text-center">
                                    {{ is_array($meal) ? implode(', ', $meal) : $meal }}
                                </span>
                            @endforeach
                        @else
                            <span>なし</span>
                        @endif
                    </p>
                </div>

                <div>
                    <p class="font-medium text-lg">食事の詳細：</p>
                    <p>{{ $record->meal_detail ?? '-' }}</p>
                </div>

                <div class="swiper mySwiper">
                    <p class="font-medium text-lg">食事の写真：</p>

                    @php
                        // JSON文字列かもしれないので decode 安全化
                        $photos = is_string($record->meal_photos)
                            ? json_decode($record->meal_photos, true)
                            : $record->meal_photos;
                    @endphp

                    <div class="swiper-wrapper">
                        @if (!empty($photos) && is_array($photos))
                            @foreach ($photos as $photo)
                                <div class="swiper-slide flex justify-center items-center">
                                    <img src="{{ asset("storage/$photo") }}"
                                        class="w-full max-h-80 object-contain rounded-lg">
                                </div>
                            @endforeach
                        @else
                            <div class="text-gray-500 mt-2">写真なし</div>
                        @endif
                    </div>

                    <div
                        class="swiper-button-prev md:block opacity-0 md:opacity-100 pointer-events-none md:pointer-events-auto">
                    </div>
                    <div
                        class="swiper-button-next md:block opacity-0 md:opacity-100 pointer-events-none md:pointer-events-auto">
                    </div>
                    <div class="swiper-pagination mt-2"></div>
                </div>

                <div class="mt-3">
                    <p class="font-medium text-lg">運動の詳細：</p>
                    <p>{{ $record->exercise_detail ?? '-' }}</p>
                </div>

                <div>
                    <p class="font-medium text-lg">運動内容：</p>
                    <p class="grid grid-cols-3 gap-3 mt-2">
                        @php
                            $exercises = is_string($record->exercises)
                                ? json_decode($record->exercises, true)
                                : $record->exercises;
                        @endphp

                        @if (!empty($exercises) && is_array($exercises))
                            @foreach ($exercises as $exercise)
                                <span class="px-2 py-1 text-sm bg-blue-50 text-blue-700 rounded-full text-center">
                                    {{ is_array($exercise) ? implode(', ', $exercise) : $exercise }}
                                </span>
                            @endforeach
                        @else
                            <span>なし</span>
                        @endif
                    </p>
                </div>

            </div>
        </div>

    </div>
@endsection
