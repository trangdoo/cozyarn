<?php

namespace App\Http\Requests\Admin\User;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate khi admin tạo tài khoản mới (route admin.users.store).
 * Authorize delegated cho middleware 'admin' đã gắn ở route group.
 */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100', 'regex:' . ValidationPatterns::NAME],
            'email'    => 'required|email:rfc|max:100|unique:users,email',
            'password' => ['required', 'string', 'min:6', 'max:100', 'confirmed', 'regex:' . ValidationPatterns::PASSWORD_STRONG],
            'phone'    => ['nullable', 'string', 'max:20', 'regex:' . ValidationPatterns::PHONE_VN],
            'address'  => 'nullable|string|max:300',
            'role'     => 'required|in:user,admin',
            'status'   => 'required|in:active,blocked',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Vui lòng nhập họ và tên.',
            'name.max'           => 'Họ và tên không được vượt quá 100 ký tự.',
            'name.regex'         => 'Họ tên chỉ được chứa chữ cái và khoảng trắng.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.email'        => 'Email không hợp lệ.',
            'email.unique'       => 'Email này đã được đăng ký.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex'     => 'Mật khẩu phải gồm cả chữ cái và chữ số.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',
            'address.max'        => 'Địa chỉ không vượt quá 300 ký tự.',
            'role.in'            => 'Vai trò không hợp lệ.',
            'status.in'          => 'Trạng thái không hợp lệ.',
        ];
    }
}
