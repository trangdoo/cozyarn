<?php

namespace App\Http\Requests\Cart;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:80', 'regex:' . ValidationPatterns::SLUG],
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
            'category.required' => 'Thiếu danh mục sản phẩm.',
            'category.regex'    => 'Danh mục không hợp lệ.',
            'slug.required'     => 'Thiếu mã sản phẩm.',
            'slug.regex'        => 'Mã sản phẩm không hợp lệ.',
            'name.required'     => 'Thiếu tên sản phẩm.',
            'image.regex'       => 'Đường dẫn ảnh không hợp lệ.',
            'price.required'    => 'Thiếu giá sản phẩm.',
            'price.min'         => 'Giá không được âm.',
            'qty.min'           => 'Số lượng tối thiểu là 1.',
            'qty.max'           => 'Số lượng tối đa là 99.',
        ];
    }
}
