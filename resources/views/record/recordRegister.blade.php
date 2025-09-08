@extends('layouts.app') {{-- 今のテンプレート名が app.blade.php の場合 --}}

@section('content')
<div class="max-w-screen-xl mx-auto">
    <h1 class=" text-2xl font-bold mb-4">データを登録(編集)する</h1>
    <p class="mb-4">
        ご登録ありがとうございます。メールに送られたリンクをクリックして、メールアドレスを認証してください。
    </p>

    @if (session('message'))
        <div class="mb-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            認証メールを再送する
        </button>
    </form>
</div>
@endsection
