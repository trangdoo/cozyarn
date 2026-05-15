<?php

namespace App\Http\Requests\Cart;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255', 'regex:' . ValidationPatterns::CART_KEY],
            'qty' => 'required|integer|min:0|max:99',
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Thiếu mã item trong giỏ.',
            'key.regex'    => 'Mã item không hợp lệ.',
            'qty.required' => 'Thiếu số lượng.',
            'qty.min'      => 'Số lượng không được âm.',
            'qty.max'      => 'Số lượng tối đa là 99.',
        ];
    }
}
