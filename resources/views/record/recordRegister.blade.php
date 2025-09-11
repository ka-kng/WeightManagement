@extends('layouts.app') {{-- 今のテンプレート名が app.blade.php の場合 --}}

@section('content')
<div class="max-w-screen-lg mx-auto px-6 mt-3">
    <h1 class=" text-2xl font-bold mb-4">データを登録(編集)する</h1>

    <form class="space-y-5 mt-10" method="POST" action="{{ route('records.store') }}" enctype="multipart/form-data">
        @csrf
        <div>
            <div class="flex items-center justify-between">
                <label for="">日付</label>
                <input
                    class="rounded-full"
                    type="date"
                    name="date"
                    value="{{ old('date') }}">
            </div>
            @error('date')
            <p class="text-red-500 text-sm text-right mt-0">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="">体重</label>
                <div>
                    <input class="w-20 rounded-full" name="weight" type="number" min="0" value="{{ old('weight') }}">
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
                    <input
                        name="sleep_hours"
                        class="w-16 rounded-full"
                        type="number"
                        min="0"
                        max="24"
                        step="1"
                        value="{{ old('sleep_hours') }}"
                        oninput="this.value = Math.min(23, Math.max(0, this.value))">
                    <span>時間</span>
                    <input
                        name="sleep_minutes"
                        class="w-16 rounded-full"
                        type="number"
                        min="0"
                        max="59"
                        step="1"
                        value="{{ old('sleep_minutes') }}"
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
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="meals[]" value="炭水化物" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>炭水化物</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="meals[]" value="タンパク質" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>タンパク質</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="meals[]" value="脂質" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>脂質</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="meals[]" value="ビタミン" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>ビタミン</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="meals[]" value="ミネラル" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>ミネラル</span>
                </label>
            </div>

            <div>
                <textarea
                    class="w-full mt-3 rounded text-left"
                    rows="3"
                    name="meal_detail"
                    id=""
                    placeholder="具体的な品名や、カロリーを入力することで、AI分析がより正確になります。"></textarea>
            </div>
            <div>
                <div x-data="{
                    previews: [],
                    previewFiles(event) {
                        const files = event.target.files;

                        if(files.length + this.previews.length > 5){
                            alert('最大5枚までです');
                            event.target.value = '';
                            return;
                        }

                        Array.from(files).forEach(file => {
                            const reader = new FileReader();
                            reader.onload = e => this.previews.push(e.target.result);
                            reader.readAsDataURL(file);
                        });
                    },
                    removePreview(index) {
                        this.previews.splice(index, 1);
                    }
                }" class="mt-4">

                    <label for="">食事写真(最大5枚)</label>

                    <input
                        type="file"
                        name="meal_photos[]"
                        id="meal_photos"
                        accept="image/*"
                        multiple
                        @change="previewFiles($event)"
                        class="block w-full text-sm text-gray-600 border rounded-lg p-2 cursor-pointer
                               file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                               file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                               hover:file:bg-blue-100">

                    <div class="flex flex-wrap gap-2 mt-3">
                        <template x-for="(src, index) in previews" :key="index">
                            <div class="relative w-20 h-20">
                                <img :src="src" class="w-20 h-20 object-cover rounded">
                                <!-- ×ボタン -->
                                <button
                                    type="button"
                                    @click="removePreview(index)"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                    ×
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="">
            <label for="">運動</label>
            <div class="grid grid-cols-3 mt-3">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="exercises[]" value="有酸素運動" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>有酸素運動</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="exercises[]" value="筋トレ" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>筋トレ</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="exercises[]" value="ストレッチ" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>ストレッチ</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="exercises[]" value="ヨガ" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>ヨガ</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="exercises[]" value="スポーツ" class="w-3 h-3 rounded-full text-blue-600 focus:ring-blue-500">
                    <span>スポーツ</span>
                </label>
            </div>

            <div>
                <textarea
                    class="w-full mt-3 rounded text-left"
                    rows="3"
                    name="exercise_detail"
                    id=""
                    placeholder="具体的にどんな運動をしたか、入力することで、AI分析がより正確になります。"></textarea>
            </div>
        </div>

        <div class="text-center">
            <button
                type="submit"
                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                保存する
            </button>
        </div>

    </form>

</div>
@endsection
