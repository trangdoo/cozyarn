<?php

namespace App\Http\Requests\Auth;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password'              => ['required', 'string', 'min:6', 'max:100', 'confirmed', 'regex:' . ValidationPatterns::PASSWORD_STRONG],
            'password_confirmation' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required'  => 'Vui lòng nhập mật khẩu mới.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex'     => 'Mật khẩu phải gồm cả chữ cái và chữ số.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}
