<?php

return [
    'ebay_accounts' => [
        ['label' => 'Tất cả tài khoản (24)', 'value' => 'all'],
        ['label' => 'us-sport-jerseys', 'value' => 'us-sport-jerseys'],
        ['label' => 'usa-fan-store', 'value' => 'usa-fan-store'],
        ['label' => 'topgear-outlet', 'value' => 'topgear-outlet'],
        ['label' => 'jersey-empire-us', 'value' => 'jersey-empire-us'],
    ],

    'notifications' => [
        'count' => 5,
        'items' => [
            [
                'avatar' => 'a3',
                'avatar_text' => '!',
                'text' => '<strong>48 listing</strong> chưa map SKU chuẩn',
                'time' => '3 PHÚT TRƯỚC',
                'route' => null,
            ],
            [
                'avatar' => 'a1',
                'avatar_text' => '₪',
                'text' => '<strong>32 đơn mới</strong> vừa kéo từ eBay',
                'time' => '18 PHÚT TRƯỚC',
                'route' => null,
            ],
            [
                'avatar' => 'a2',
                'avatar_text' => '✓',
                'text' => '<strong>topgear-outlet</strong> đã đồng bộ token',
                'time' => '1 GIỜ TRƯỚC',
                'route' => null,
            ],
        ],
        'footer' => [
            'text'  => 'Xem tất cả →',
            'route' => null,
        ],
    ],

    'profile_menu' => [
        [
            'text'  => 'Hồ sơ',
            'route' => 'admin.profile.edit',
            'icon'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        ],
        [
            'text'  => 'Phân quyền',
            'route' => null,
            'icon'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        ],
    ],

    'footer' => [
        'copyright' => '© :year · :app — eBay Catalog & Order Management',
        'meta' => [
            'Laravel API',
        ],
    ],
];
