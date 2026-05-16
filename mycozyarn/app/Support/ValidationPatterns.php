<?php

namespace App\Support;

/**
 * Tập trung các pattern regex dùng chung cho validate input.
 * Đặt ở support để mọi FormRequest / controller có thể chia sẻ — sửa 1 chỗ
 * khi yêu cầu định dạng thay đổi (vd: thắt chặt phone, mở rộng slug).
 */
final class ValidationPatterns
{
    /**
     * Họ tên: chữ cái Unicode (gồm tiếng Việt có dấu), khoảng trắng, dấu ., ', - .
     * Tối thiểu 2 ký tự, tối đa do FormRequest tự đặt với rule `max`.
     */
    public const NAME = "/^[\\p{L}][\\p{L}\\p{M}\\s\\.\\'\\-]{1,99}$/u";

    /**
     * Slug: chỉ chữ thường, số, dấu gạch ngang. Bắt đầu/kết thúc bằng chữ-số.
     */
    public const SLUG = '/^[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?$/';

    /**
     * SĐT Việt Nam — chấp nhận: bắt đầu bằng 0 hoặc +84, theo sau là 9 chữ số,
     * cho phép khoảng trắng và dấu gạch giữa các nhóm số.
     */
    public const PHONE_VN = '/^(?:\+84|0)[\s\-]?[3-9](?:[\s\-]?\d){8}$/';

    /**
     * Phone "lỏng" cho admin/checkout: 9–20 ký tự, chỉ cho phép số, +, khoảng trắng, gạch.
     */
    public const PHONE_LOOSE = '/^[0-9+\\s\\-]{9,20}$/';

    /**
     * Đường dẫn ảnh: /something/...jpg|png|webp|gif|svg HOẶC URL http(s).
     * Nới lỏng để chấp nhận query string sau dấu ?.
     */
    public const IMAGE_PATH = '/^(?:\\/[\\w\\-\\.\\/]+\\.(?:jpe?g|png|webp|gif|svg)(?:\\?[\\w=&%\\-\\.]*)?|https?:\\/\\/[\\w\\-\\.\\/?#=&%@:]+)$/i';

    /**
     * Mật khẩu mạnh: tối thiểu 6 ký tự, ít nhất 1 chữ và 1 chữ số.
     * Không bắt buộc ký tự đặc biệt để giảm friction cho người dùng phổ thông.
     */
    public const PASSWORD_STRONG = '/^(?=.*[A-Za-z])(?=.*\d).{6,100}$/';

    /**
     * Tag sản phẩm: chữ Unicode, số và khoảng trắng, 1–30 ký tự.
     */
    public const TAG = "/^[\\p{L}\\p{N}\\s\\-]{1,30}$/u";

    /**
     * Đơn vị (cuộn, cái, gói, set...): chữ Unicode, 1–30 ký tự.
     */
    public const UNIT = "/^[\\p{L}\\p{N}\\s\\.\\-]{1,30}$/u";

    /**
     * Mã đơn hàng tự sinh dạng "CZ" + 8 ký tự hex.
     */
    public const ORDER_ID = '/^CZ[A-F0-9]{8}$/';

    /**
     * Cart key: format `category|slug|variant|size` (xem App\Support\Cart::makeKey).
     * Variant label có thể là tiếng Việt có dấu ("Pastel hồng"), size có thể rỗng.
     * Vì vậy regex phải cho phép Unicode letter, `|` làm separator, và segment rỗng
     * (vd: "len-soi|len-cotton-pastel|Pastel hồng|" — size để trống).
     */
    public const CART_KEY = "/^[\\p{L}\\p{N}][\\p{L}\\p{N}\\s\\-\\._:|]{0,254}$/u";

    /**
     * Item key trong order — cùng định dạng cart key (forwarded từ Cart::makeKey
     * khi order được tạo).
     */
    public const ITEM_KEY = "/^[\\p{L}\\p{N}][\\p{L}\\p{N}\\s\\-\\._:|]{0,254}$/u";
}
