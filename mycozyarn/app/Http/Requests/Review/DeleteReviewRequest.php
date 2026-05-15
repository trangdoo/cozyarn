<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class DeleteReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // id review nội bộ có dạng "{order_id}::{item_key}" — cho phép cả 2 phần
            'id' => ['required', 'string', 'max:300', 'regex:/^CZ[A-F0-9]{8}::[a-z0-9][a-z0-9\-:_\.]{0,200}$/i'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'Thiếu mã đánh giá.',
            'id.regex'    => 'Mã đánh giá không hợp lệ.',
        ];
    }
}
