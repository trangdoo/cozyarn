<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240|mimes:csv,txt,json,xml',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn file để nhập.',
            'file.max'      => 'File không được vượt quá 10MB.',
            'file.mimes'    => 'Chỉ chấp nhận file CSV, JSON, XML hoặc TXT.',
        ];
    }
}
