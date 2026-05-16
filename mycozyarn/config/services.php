<?php

return [



    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [ 
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sepay' => [
        // Cấu hình trong trang SePay (Cài đặt → Webhook → API Key).
        // SePay sẽ gửi `Authorization: Apikey <key>` trong mỗi request.
        'api_key' => env('SEPAY_API_KEY'),

        // Thông tin tài khoản nhận chuyển khoản — hiển thị + sinh ảnh VietQR ở trang
        // checkout success khi user chọn payment=bank. Để trống → trang sẽ chỉ báo
        // "shop sẽ liên hệ gửi QR" như cũ (graceful fallback).
        'bank' => [
            'bank'           => env('SEPAY_BANK', 'VCB'),       // mã ngân hàng theo SePay/VietQR (vd: VCB, MB, TCB)
            'bank_name'      => env('SEPAY_BANK_NAME', ''),     // tên hiển thị (vd: "Vietcombank")
            'account_number' => env('SEPAY_ACCOUNT_NUMBER', ''),
            'account_name'   => env('SEPAY_ACCOUNT_NAME', ''),
        ],
    ],

];
