<?php

/**
 * Cấu hình theme cho package igaster/laravel-theme.
 *
 * Mỗi theme có:
 *   - views-path: nơi chứa override Blade (so với resources/views/)
 *   - asset-path: nơi chứa CSS/JS/img (so với public/)
 *   - extends   : kế thừa từ theme khác (view/asset không có sẽ rơi xuống theme cha)
 *
 * Theme "cozy" là theme mặc định — chính là giao diện hiện tại của shop.
 * Asset path "themes/cozy" rỗng nên các view fallback về resources/views/ (giao diện gốc).
 * "mint" và "night" extend cozy, chỉ override file skin.css để đổi màu.
 */
return [

    'themes_path' => null,                  // dùng mặc định: resources/views
    'asset_not_found' => 'LOG_ERROR',
    'default' => 'cozy',                    // theme khởi tạo nếu chưa có ai set
    'cache' => false,
    'register_blade_directives' => true,

    'themes' => [
        'cozy' => [
            'extends'    => null,
            'views-path' => 'themes/cozy',  // resources/views/themes/cozy/ — rỗng → fallback views gốc
            'asset-path' => 'themes/cozy',  // public/themes/cozy/
        ],
        'mint' => [
            'extends'    => 'cozy',
            'views-path' => 'themes/mint',
            'asset-path' => 'themes/mint',
        ],
        'night' => [
            'extends'    => 'cozy',
            'views-path' => 'themes/night',
            'asset-path' => 'themes/night',
        ],
    ],
];
