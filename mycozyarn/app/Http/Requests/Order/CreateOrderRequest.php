<?php

namespace App\Http\Requests\Order;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate khi user submit thanh toán (route checkout.place).
 * Phone bắt buộc theo định dạng VN; tên có pattern Unicode tiếng Việt.
 */
class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100', 'regex:' . ValidationPatterns::NAME],
            'phone'    => ['required', 'string', 'max:20',  'regex:' . ValidationPatterns::PHONE_VN],
            'province' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'address'  => 'required|string|max:255',
            'note'     => 'nullable|string|max:500',
            'payment'  => 'required|in:cod,bank,momo',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Vui lòng nhập họ và tên người nhận.',
            'name.regex'        => 'Họ tên chỉ chứa chữ cái và khoảng trắng.',
            'phone.required'    => 'Vui lòng nhập số điện thoại.',
            'phone.regex'       => 'Số điện thoại không hợp lệ (vd: 0987654321 hoặc +84987654321).',
            'province.required' => 'Vui lòng chọn tỉnh/thành.',
            'district.required' => 'Vui lòng chọn quận/huyện.',
            'address.required'  => 'Vui lòng nhập địa chỉ chi tiết.',
            'payment.in'        => 'Phương thức thanh toán không hợp lệ.',
        ];
    }
}
