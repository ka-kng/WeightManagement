<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = auth()->id();
        $ignoreId = $this->route('record')?->id;

        $dateRule = Rule::unique('records')->where(fn($q) => $q->where('user_id', $userId));
        if ($ignoreId) {
            $dateRule->ignore($ignoreId);
        }

        return [
            'date' => ['required', 'date', $dateRule],
            'weight' => 'required|numeric|min:0',
            'sleep_hours' => 'required|integer|min:0|max:23',
            'sleep_minutes' => 'required|integer|min:0|max:59',
            'meals' => 'nullable|array',
            'meal_detail' => 'nullable|string',
            'meal_photos' => 'nullable|array|max:5',
            'meal_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'exercises' => 'nullable|array',
            'exercise_detail' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'date.unique' => 'この日付の記録はすでに登録されています。',
            'meal_photos.max' => '画像は最大5枚までアップロードできます。',
            'meal_photos.*.max' => '1ファイルの容量は最大5MBまでです。',
            'meal_photos.*.image' => '画像ファイルのみアップロードできます。',
            'meal_photos.*.mimes' => '許可されている形式は jpeg, png, jpg, gif です。',
        ];
    }
}
