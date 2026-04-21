<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:100',
            'phone'   => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-]{9,20}$/'],
            'address' => 'nullable|string|max:500',
            'avatar'  => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.max'      => 'Họ và tên không được vượt quá 100 ký tự.',
            'phone.regex'   => 'Số điện thoại không hợp lệ.',
            'address.max'   => 'Địa chỉ không được vượt quá 500 ký tự.',
        ];
    }
}
