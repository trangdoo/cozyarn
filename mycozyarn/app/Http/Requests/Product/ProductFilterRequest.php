<?php

namespace App\Http\Requests\Product;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate query string khi lọc danh sách sản phẩm (admin & user).
 * Tránh việc người dùng truyền giá trị bất thường vào URL gây query lỗi.
 */
class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q'        => 'nullable|string|max:120',
            'category' => ['nullable', 'string', 'max:80', 'regex:/^(?:all|[a-z0-9][a-z0-9\-]{0,80})$/'],
            'status'   => 'nullable|in:all,active,inactive',
            'sort'     => 'nullable|in:updated_desc,created_desc,name_asc,name_desc,price_asc,price_desc',
            'page'     => 'nullable|integer|min:1|max:9999',
        ];
    }

    public function messages(): array
    {
        return [
            'category.regex' => 'Slug danh mục không hợp lệ.',
            'status.in'      => 'Trạng thái lọc không hợp lệ.',
            'sort.in'        => 'Cách sắp xếp không hợp lệ.',
        ];
    }

    /**
     * Tiện cho controller: lấy filter đã chuẩn hoá kèm default.
     */
    public function filters(): array
    {
        return [
            'q'        => trim((string) $this->input('q', '')),
            'category' => $this->input('category', 'all'),
            'status'   => $this->input('status', 'all'),
            'sort'     => $this->input('sort', 'updated_desc'),
        ];
    }
}
