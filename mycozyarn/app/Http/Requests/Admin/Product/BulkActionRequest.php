<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'ids'   => 'required|array|min:1|max:500',
            // ids có thể là số nguyên hoặc dạng "{cat_slug}::{product_slug}"
            'ids.*' => ['required', 'string', 'max:300', 'regex:/^(?:\d+|[a-z0-9][a-z0-9\-]*::[a-z0-9][a-z0-9\-]*)$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Chưa chọn sản phẩm nào.',
            'ids.array'    => 'Dữ liệu không hợp lệ.',
            'ids.max'      => 'Chỉ xử lý tối đa 500 mục cùng lúc.',
            'ids.*.regex'  => 'Có mã sản phẩm không hợp lệ trong danh sách.',
        ];
    }
}
