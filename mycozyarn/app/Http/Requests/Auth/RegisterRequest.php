<?php

namespace App\Http\Requests\Auth;

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
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|max:100|confirmed',
            'phone'    => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-]{9,20}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Vui lòng nhập họ và tên.',
            'name.max'           => 'Họ và tên không được vượt quá 100 ký tự.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.email'        => 'Email không hợp lệ.',
            'email.unique'       => 'Email này đã được đăng ký.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',
        ];
    }
}
