<?php

namespace App\Http\Requests\Cart;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

class RemoveFromCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255', 'regex:' . ValidationPatterns::CART_KEY],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Thiếu mã item trong giỏ.',
            'key.regex'    => 'Mã item không hợp lệ.',
        ];
    }
}
