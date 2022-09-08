<?php

return [
    // 'dashboard' => [
    //     'route' => 'core.dashboard',
    //     'permission' => [],
    //     'class' => '',
    //     'icon' => 'fa fa-dashboard',
    //     'name' => 'dashboard',
    //     'text' => 'core::menu.dashboard',
    //     'order' => 1,
    //     'sub' => []
    // ],
//     'settings' => [
// //        'route'      => 'core.settings.index',
//         'permission' => [103],
//         'class' => '',
//         'icon' => 'fa fa-cogs',
//         'name' => 'settings',
//         'text' => 'core::menu.settings',
//         'order' => 9,
//         'sub' => [
//             [
//                 'route' => 'core.settings.index',
//                 'permission' => [103],
//                 'class' => '',
//                 'icon' => 'fa fa-cogs',
//                 'name' => 'settings',
//                 'text' => 'core::menu.settings',
//                 'order' => 1,
//                 'sub' => []
//             ],
//             [
//                 'route' => 'core.menu.index',
//                 'permission' => ['core.menu.index'],
//                 'class' => '',
//                 'icon' => 'fa fa-bars',
//                 'name' => 'menus',
//                 'text' => 'core::menu.menus',
//                 'order' => 2,
//                 'sub' => []
//             ],
//             [
//                 'route' => 'core.menu_type.index',
//                 'permission' => ['core.menu_type.index'],
//                 'class' => '',
//                 'icon' => 'fa fa-bars',
//                 'name' => 'menu_types',
//                 'text' => 'core::menu.menu_type',
//                 'order' => 3,
//                 'sub' => []
//             ],
//             'product_manager' => [
//                 'text' => 'product::menu.manager',
//                 'route' => 'product.index',
//                 'permission' => [60],
//                 'class' => '',
//                 'icon' => 'fa fa-th-large',
//                 'name' => 'setting',
//                 'order' => 4,
//                 'sub' => [
//                     'product_categories' => [
//                         'route' => 'product.categories.index',
//                         'permission' => [52],
//                         'class' => '',
//                         'icon' => 'fa fa-bars',
//                         'name' => 'product_categories',
//                         'text' => 'product::menu.product.categories',
//                         'order' => 1,
//                         'sub' => []
//                     ],
//                     'product_list' => [
//                         'route' => 'product.index',
//                         'permission' => [60],
//                         'class' => '',
//                         'icon' => 'fa fa-th-large',
//                         'name' => 'product_list',
//                         'text' => 'product::menu.products',
//                         'order' => 2,
//                         'sub' => []
//                     ],
//                     'commissions' => [
//                         'route' => 'product.commissions.index',
//                         'permission' => [60],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'commissions',
//                         'text' => 'product::menu.product.commissions',
//                         'order' => 3,
//                         'sub' => []
//                     ],
//                     'product_agency_commissions' => [
//                         'route' => 'product.agency_commissions.index',
//                         'permission' => [60],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'product_agency_commissions',
//                         'text' => 'product::menu.product.agency_commissions',
//                         'order' => 4,
//                         'sub' => []
//                     ],
//                     'product_coupons' => [
//                         'route' => 'product.coupons.index',
//                         'permission' => [60],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'coupons',
//                         'text' => 'product::menu.product.coupons',
//                         'order' => 5,
//                         'sub' => []
//                     ],
//                     'product_category_class' => [
//                         'route' => 'product.category_class.list_category',
//                         'permission' => [20, 80],
//                         'class' => '',
//                         'icon' => 'fa fa-bars',
//                         'name' => 'product_category_classes',
//                         'text' => 'product::menu.product.category_classes',
//                         'order' => 6,
//                         'sub' => []
//                     ],
//                 ]
//             ],
//             'users' => [
//                 'route' => 'core.user.index',
//                 'permission' => [84],
//                 'class' => '',
//                 'icon' => 'fa fa-user',
//                 'name' => 'setting',
//                 'text' => 'core::menu.users',
//                 'order' => 5,
//                 'sub' => [
//                     [
//                         'route' => 'core.user.index',
//                         'permission' => [84],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'users',
//                         'text' => 'core::menu.users',
//                         'order' => 1,
//                         'sub' => []
//                     ],
//                     [
//                         'route' => 'core.role.index',
//                         'permission' => [88],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'roles',
//                         'text' => 'core::menu.roles',
//                         'order' => 2,
//                         'sub' => []
//                     ],
//                     [
//                         'route' => 'core.group.index',
//                         'permission' => [92],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'groups',
//                         'text' => 'core::menu.groups',
//                         'order' => 3,
//                         'sub' => []
//                     ]
//                 ]
//             ],
//             'insurance_manager' => [
//                 'route' => 'insurance.company.index',
//                 'text' => 'insurance::menu.manager',
//                 'permission' => [20, 24, 8, 36, 48],
//                 'class' => '',
//                 'icon' => 'fa fa-bars',
//                 'name' => 'setting',
//                 'order' => 6,
//                 'sub' => [
//                     'insurance_company' => [
//                         'route' => 'insurance.company.index',
//                         'permission' => [20],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'insurance_company',
//                         'text' => 'insurance::menu.company',
//                         'order' => 1,
//                         'sub' => []
//                     ],
//                     'insurance_type' => [
//                         'route' => 'insurance.type.index',
//                         'permission' => [24],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'insurance_type',
//                         'text' => 'insurance::menu.insurance_type',
//                         'order' => 1,
//                         'sub' => []
//                     ],
//                     'customer_type' => [
//                         'route' => 'insurance.customer_type.index',
//                         'permission' => [8],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'customer_type',
//                         'text' => 'insurance::menu.customer_type',
//                         'order' => 1,
//                         'sub' => []
//                     ],
//                     'beneficiary_type' => [
//                         'route' => 'insurance.beneficiary_type.index',
//                         'permission' => [36],
//                         'class' => '',
//                         'icon' => 'fa fa-user',
//                         'name' => 'customer_type',
//                         'text' => 'insurance::menu.beneficiary_type',
//                         'order' => 1,
//                         'sub' => []
//                     ],
//                     'formula' => [
//                         'route' => 'insurance.formula.index',
//                         'permission' => [48],
//                         'class' => '',
//                         'icon' => 'fa fa-code',
//                         'name' => 'insurance_formula',
//                         'text' => 'insurance::menu.formula',
//                         'order' => 1,
//                         'sub' => []
//                     ],
//                 ]
//             ],
//         ]
//     ],
];
