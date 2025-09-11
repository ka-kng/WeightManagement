@extends('layouts.app') {{-- 今のテンプレート名が app.blade.php の場合 --}}

@section('content')
<div class="max-w-screen-lg mx-auto">
    <div class="flex justify-between items-center px-6 mt-3">
        <h1 class="text-xl font-bold">記録一覧</h1>
        <a class="text-blue-600" href="{{ route('records.create') }}">+記録する</a>
    </div>

    <div class="bg-white border ">
        @foreach($records as $record)
            <p>{{ $record->date->format('Y-m-d') }}</p>
            <p>{{ number_format($record->weight, 1) }}</p>
            <p>{{ $record->sleep_hours }}</p>
        @endforeach
    </div>

</div>
@endsection
