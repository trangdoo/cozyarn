<?php

namespace App\Http\Requests\Admin\User;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate khi admin cập nhật tài khoản (route admin.users.update).
 * Email phải unique TRỪ chính user đang sửa — dùng route param {user} để loại trừ.
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'    => ['required', 'string', 'max:100', 'regex:' . ValidationPatterns::NAME],
            'email'   => ['required', 'email:rfc', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'phone'   => ['nullable', 'string', 'max:30', 'regex:' . ValidationPatterns::PHONE_VN],
            'address' => 'nullable|string|max:300',
            'role'    => 'required|in:user,admin',
            'status'  => 'required|in:active,blocked',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Vui lòng nhập họ và tên.',
            'name.max'       => 'Họ và tên không được vượt quá 100 ký tự.',
            'name.regex'     => 'Họ tên chỉ được chứa chữ cái và khoảng trắng.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email'    => 'Email không hợp lệ.',
            'email.unique'   => 'Email này đã được sử dụng bởi tài khoản khác.',
            'phone.regex'    => 'Số điện thoại không hợp lệ.',
            'address.max'    => 'Địa chỉ không vượt quá 300 ký tự.',
            'role.in'        => 'Vai trò không hợp lệ.',
            'status.in'      => 'Trạng thái không hợp lệ.',
        ];
    }
}
