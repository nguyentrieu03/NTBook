<?php

return [
    'brand' => [
        'name' => env('APP_NAME', 'SKU Hub'),
        'tag'  => 'eBay · omnichannel',
    ],

    'sections' => [
        [
            'label' => 'Tổng quan',
            'items' => [
                [
                    'key'   => 'dashboard',
                    'text'  => 'Dashboard',
                    'route' => 'admin.dashboard',
                    'icon'  => '<path d="M3 12 12 3l9 9"/><path d="M5 10v10h14V10"/>',
                ],
            ],
        ],
        [
            'label' => 'Catalog',
            'items' => [
                [
                    'key'   => 'products',
                    'text'  => 'Sản phẩm',
                    'route' => 'admin.products.index',
                    'icon'  => '<path d="M20 7 12 3 4 7l8 4 8-4z"/><path d="M4 7v10l8 4 8-4V7"/><path d="M12 11v10"/>',
                ],
                [
                    'key'   => 'product-form',
                    'text'  => 'Thêm / Sửa sản phẩm',
                    'route' => 'admin.products.create',
                    'icon'  => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"/>',
                ],
                [
                    'key'   => 'categories',
                    'text'  => 'Danh mục & Thuộc tính',
                    'route' => 'admin.categories.index',
                    'icon'  => '<path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-7L10 4H5a2 2 0 0 0-2 2z"/>',
                ],
            ],
        ],
        // [
        //     'label' => 'Kênh eBay',
        //     'items' => [
        //         [
        //             'key'   => 'accounts',
        //             'text'  => 'Tài khoản eBay',
        //             'route' => null,
        //             'icon'  => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/>',
        //         ],
        //         [
        //             'key'     => 'listings',
        //             'text'    => 'Đối soát Listing / SKU',
        //             'route'   => null,
        //             'badge'   => ['kind' => 'hot', 'text' => '48'],
        //             'icon'    => '<path d="M4 6h16M4 12h16M4 18h10"/><circle cx="19" cy="18" r="2.4"/>',
        //         ],
        //     ],
        // ],
        // [
        //     'label' => 'Bán hàng',
        //     'items' => [
        //         [
        //             'key'   => 'orders',
        //             'text'  => 'Đơn hàng',
        //             'route' => null,
        //             'icon'  => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/>',
        //         ],
        //         [
        //             'key'   => 'analytics',
        //             'text'  => 'Thống kê bán chạy',
        //             'route' => 'admin.dashboard',
        //             'icon'  => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-4 4"/>',
        //         ],
        //     ],
        // ],
        // [
        //     'label' => 'Hệ thống',
        //     'items' => [
        //         [
        //             'key'   => 'users',
        //             'text'  => 'Người dùng & Phân quyền',
        //             'route' => null,
        //             'icon'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/>',
        //         ],
        //     ],
        // ],
    ],
];
