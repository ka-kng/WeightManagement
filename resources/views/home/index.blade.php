@extends('layouts.app')

@section('content')
    <div class="max-w-screen-lg mx-auto px-6">
        <div class="flex justify-between items-center mt-3">
            <h1 class="text-lg font-bold">ホーム（直近の記録）</h1>
        </div>
        <div>
            <p class="mt-3">目標体重：{{ number_format($record->user->target_weight, 1) }} kg</p>
        </div>

        <div>
            
        </div>

    </div>
@endsection
