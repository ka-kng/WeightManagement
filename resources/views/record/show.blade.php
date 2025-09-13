@extends('layouts.app')

@section('content')
    <div class="max-w-screen-lg mx-auto px-6">
        <div class="flex justify-between items-center mt-3">
            <h1 class="text-xl font-bold">記録一覧</h1>
            <div class="flex items-center gap-3">
                <a class="text-red-600" href="{{ route('records.create') }}">削除する</a>
                <a class="text-blue-600" href="{{ route('records.edit', $record->id) }}">編集する</a>
            </div>
        </div>

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

                <div class="">
                    <p class="font-medium text-lg">摂取内容：</p>
                    <p class="grid grid-cols-3 gap-3">
                        @if ($record->meals)
                            @foreach (json_decode($record->meals, true) as $meal)
                                <span class="px-2 py-1 mt-2 text-sm bg-blue-50 text-blue-700 rounded-full text-center">
                                    {{ $meal }}
                                </span>
                            @endforeach
                        @else
                            <span>なし</span>
                        @endif
                    </p>
                </div>

                <div>
                    <p class="font-medium text-lg">食事の詳細：</p>
                    <p>{{ $record->meal_detail }}</p>
                </div>

                <div class="swiper mySwiper">
                  <p class="font-medium text-lg">食事の写真：</p>
                    <div class="swiper-wrapper">
                        @foreach (json_decode($record->meal_photos ?? '[]', true) as $photo)
                            <div class="swiper-slide flex justify-center items-center bg-gray-100">
                                <img src="{{ asset("storage/$photo") }}" class="w-full max-h-80 object-contain rounded-lg">
                            </div>
                        @endforeach
                    </div>
                    <div
                        class="swiper-button-prev md:block opacity-0 md:opacity-100 pointer-events-none md:pointer-events-auto">
                    </div>
                    <div
                        class="swiper-button-next md:block opacity-0 md:opacity-100 pointer-events-none md:pointer-events-auto">
                    </div>

                    <!-- ページネーション -->
                    <div class="swiper-pagination mt-2"></div>
                </div>

                <div class="">
                    <p class="font-medium text-lg">運動内容：</p>
                    <p class="grid grid-cols-3 gap-3 mt-2">
                        @if ($record->exercises)
                            @foreach (json_decode($record->exercises, true) as $exercise)
                                <span class="px-2 py-1 text-sm bg-blue-50 text-blue-700 rounded-full text-center">
                                    {{ $exercise }}
                                </span>
                            @endforeach
                        @else
                            <span>なし</span>
                        @endif
                    </p>
                </div>

                <div>
                    <p class="font-medium text-lg">運動の詳細：</p>
                    <p>{{ $record->meal_detail }}</p>
                </div>

            </div>
        </div>

    </div>
@endsection
