<?php

namespace App\Http\Requests\Admin\Category;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name'        => 'required|string|max:100',
            'slug'        => [
                'nullable',
                'string',
                'max:150',
                'regex:' . ValidationPatterns::SLUG,
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'description' => 'nullable|string|max:500',
            'image'       => ['nullable', 'string', 'max:255', 'regex:' . ValidationPatterns::IMAGE_PATH],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Vui lòng nhập tên danh mục.',
            'name.max'        => 'Tên danh mục không vượt quá 100 ký tự.',
            'slug.regex'      => 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang.',
            'slug.unique'     => 'Slug đã được sử dụng bởi danh mục khác.',
            'description.max' => 'Mô tả không vượt quá 500 ký tự.',
            'image.regex'     => 'Đường dẫn ảnh không hợp lệ.',
        ];
    }
}
