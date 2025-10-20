<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MypageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ログインユーザーのみ許可
        return Auth::check();
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'height' => ['required', 'numeric'],
            'target_weight' => ['required', 'numeric'],
            'gender' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
        ];
    }
}
