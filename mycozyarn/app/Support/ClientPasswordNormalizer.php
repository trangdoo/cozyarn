<?php

namespace App\Support;

/**
 * Client băm SHA-256(password) trước khi gửi (resources/js/auth-validate.js).
 * Helper này áp dụng cùng phép băm ở server khi client KHÔNG hash (vd: JS bị
 * tắt hoặc trình duyệt không có Web Crypto). Nhờ đó dù đường nào, ta cũng
 * lưu cùng một giá trị `bcrypt(SHA256(plaintext))` trong DB.
 *
 * Cách phát hiện đã hash: 64 ký tự hex (chuỗi dài 64 chỉ chứa [a-f0-9]).
 */
final class ClientPasswordNormalizer
{
    /** Trả về SHA-256 hex của plaintext, hoặc giữ nguyên nếu input đã là hex. */
    public static function normalize(?string $value): string
    {
        $value = (string) $value;
        if ($value === '') return '';

        if (preg_match('/^[a-f0-9]{64}$/', $value) === 1) {
            return $value;
        }
        return hash('sha256', $value);
    }
}
