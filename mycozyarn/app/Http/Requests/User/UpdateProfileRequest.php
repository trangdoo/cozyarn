<?php

namespace App\Http\Requests\User;

use App\Support\ValidationPatterns;
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
            'name'    => ['required', 'string', 'max:100', 'regex:' . ValidationPatterns::NAME],
            'phone'   => ['nullable', 'string', 'max:20', 'regex:' . ValidationPatterns::PHONE_VN],
            'address' => 'nullable|string|max:500',
            'avatar'  => ['nullable', 'string', 'max:255', 'regex:' . ValidationPatterns::IMAGE_PATH],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.max'      => 'Họ và tên không được vượt quá 100 ký tự.',
            'name.regex'    => 'Họ tên chỉ được chứa chữ cái, khoảng trắng và dấu cách.',
            'phone.regex'   => 'Số điện thoại không hợp lệ (vd: 0987654321 hoặc +84987654321).',
            'address.max'   => 'Địa chỉ không được vượt quá 500 ký tự.',
            'avatar.regex'  => 'Đường dẫn ảnh đại diện không hợp lệ.',
        ];
    }
}
