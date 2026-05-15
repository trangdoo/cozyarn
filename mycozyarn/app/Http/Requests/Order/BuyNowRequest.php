<?php

namespace App\Http\Requests\Order;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

class BuyNowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:80',  'regex:' . ValidationPatterns::SLUG],
            'slug'     => ['required', 'string', 'max:255', 'regex:' . ValidationPatterns::SLUG],
            'name'     => 'required|string|max:255',
            'image'    => ['nullable', 'string', 'max:300', 'regex:' . ValidationPatterns::IMAGE_PATH],
            'price'    => 'required|integer|min:0|max:1000000000',
            'qty'      => 'nullable|integer|min:1|max:99',
            'variant'  => 'nullable|string|max:80',
            'size'     => 'nullable|string|max:30',
        ];
    }

    public function messages(): array
    {
        return [
            'category.regex' => 'Danh mục không hợp lệ.',
            'slug.regex'     => 'Mã sản phẩm không hợp lệ.',
            'image.regex'    => 'Đường dẫn ảnh không hợp lệ.',
            'price.min'      => 'Giá không được âm.',
        ];
    }
}
