<?php

namespace App\Http\Requests\User;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate đổi mật khẩu cho user đang đăng nhập.
 * Việc kiểm tra mật khẩu hiện tại có khớp hay không nằm ở UserService::changePassword().
 */
class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string|max:100',
            'new_password'     => [
                'required',
                'string',
                'min:6',
                'max:100',
                'confirmed',
                'different:current_password',
                'regex:' . ValidationPatterns::PASSWORD_STRONG,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required'     => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min'          => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.max'          => 'Mật khẩu mới không vượt quá 100 ký tự.',
            'new_password.regex'        => 'Mật khẩu phải gồm cả chữ cái và chữ số.',
            'new_password.confirmed'    => 'Mật khẩu mới nhập lại không khớp.',
            'new_password.different'    => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ];
    }
}
