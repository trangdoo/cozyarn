<?php

namespace App\Http\Requests\Review;

use App\Support\ValidationPatterns;
use Illuminate\Foundation\Http\FormRequest;

class CreateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string', 'max:30', 'regex:' . ValidationPatterns::ORDER_ID],
            'item_key' => ['required', 'string', 'max:255', 'regex:' . ValidationPatterns::ITEM_KEY],
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Thiếu mã đơn hàng.',
            'order_id.regex'    => 'Mã đơn hàng không hợp lệ.',
            'item_key.required' => 'Thiếu mã sản phẩm cần đánh giá.',
            'item_key.regex'    => 'Mã sản phẩm không hợp lệ.',
            'rating.required'   => 'Vui lòng chọn số sao.',
            'rating.min'        => 'Chọn ít nhất 1 sao.',
            'rating.max'        => 'Tối đa 5 sao.',
            'comment.max'       => 'Bình luận không vượt quá 1000 ký tự.',
        ];
    }
}
