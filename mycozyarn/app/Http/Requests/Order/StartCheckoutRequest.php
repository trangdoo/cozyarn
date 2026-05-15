<?php

namespace App\Http\Requests\Order;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

class StartCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keys'   => 'required|array|min:1|max:99',
            'keys.*' => ['required', 'string', 'max:255', 'regex:' . ValidationPatterns::CART_KEY],
        ];
    }

    public function messages(): array
    {
        return [
            'keys.required' => 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.',
            'keys.min'      => 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.',
            'keys.*.regex'  => 'Có mục giỏ hàng không hợp lệ.',
        ];
    }
}
