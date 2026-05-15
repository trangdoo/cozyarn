<?php

namespace App\Http\Requests\Admin\Category;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'image'       => ['nullable', 'string', 'max:255', 'regex:' . ValidationPatterns::IMAGE_PATH],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Vui lòng nhập tên danh mục.',
            'name.max'         => 'Tên danh mục không vượt quá 100 ký tự.',
            'description.max'  => 'Mô tả không vượt quá 500 ký tự.',
            'image.max'        => 'Đường dẫn ảnh không vượt quá 255 ký tự.',
            'image.regex'      => 'Đường dẫn ảnh không hợp lệ (vd: /images/abc.jpg hoặc https://...).',
        ];
    }
}
