<?php

namespace App\Http\Requests\Auth;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100', 'regex:' . ValidationPatterns::NAME],
            'email'    => 'required|email:rfc,strict|max:100|unique:users,email',
            'password' => ['required', 'string', 'min:6', 'max:100', 'confirmed', 'regex:' . ValidationPatterns::PASSWORD_STRONG],
            'phone'    => ['nullable', 'string', 'max:20', 'regex:' . ValidationPatterns::PHONE_VN],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Vui lòng nhập họ và tên.',
            'name.max'           => 'Họ và tên không được vượt quá 100 ký tự.',
            'name.regex'         => 'Họ tên chỉ được chứa chữ cái, khoảng trắng và dấu cách.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.email'        => 'Email không hợp lệ.',
            'email.unique'       => 'Email này đã được đăng ký.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex'     => 'Mật khẩu phải gồm cả chữ cái và chữ số.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'phone.regex'        => 'Số điện thoại không hợp lệ (vd: 0987654321 hoặc +84987654321).',
        ];
    }
}
