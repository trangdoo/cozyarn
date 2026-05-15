<?php

namespace App\Http\Requests\Admin\Product;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name'          => 'required|string|max:255',
            'category_slug' => ['required', 'string', 'max:80', 'regex:' . ValidationPatterns::SLUG, Rule::exists('categories', 'slug')],
            'slug'          => [
                'nullable',
                'string',
                'max:255',
                'regex:' . ValidationPatterns::SLUG,
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'shortDesc'     => 'required|string|max:500',
            'desc'          => 'nullable|string|max:3000',
            'price'         => 'required|integer|min:0|max:1000000000',
            'oldPrice'      => 'nullable|integer|min:0|max:1000000000',
            'quantity'      => 'required|integer|min:0|max:1000000',
            'unit'          => ['required', 'string', 'max:30', 'regex:' . ValidationPatterns::UNIT],
            'image'         => ['nullable', 'string', 'max:255', 'regex:' . ValidationPatterns::IMAGE_PATH],
            'tag'           => ['nullable', 'string', 'max:30', 'regex:' . ValidationPatterns::TAG],
            'status'        => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Vui lòng nhập tên sản phẩm.',
            'category_slug.required' => 'Vui lòng chọn danh mục.',
            'category_slug.exists'   => 'Danh mục không tồn tại.',
            'category_slug.regex'    => 'Slug danh mục không hợp lệ.',
            'slug.regex'             => 'Slug chỉ chứa chữ thường, số và dấu gạch ngang.',
            'slug.unique'            => 'Slug đã được dùng.',
            'shortDesc.required'     => 'Vui lòng nhập mô tả ngắn.',
            'price.required'         => 'Vui lòng nhập giá.',
            'quantity.required'      => 'Vui lòng nhập số lượng.',
            'unit.required'          => 'Vui lòng nhập đơn vị.',
            'unit.regex'             => 'Đơn vị chỉ chứa chữ và số.',
            'image.regex'            => 'Đường dẫn ảnh không hợp lệ.',
            'tag.regex'              => 'Tag chỉ được chứa chữ cái, số và khoảng trắng.',
            'status.in'              => 'Trạng thái không hợp lệ.',
        ];
    }
}
