<?php 
return [
    "name" => "客户管理",
    "version" => "1.0",
    "description" => "客户管理。",
    'dialogs' => [
        'customer_price' => [
            'name' => '客户销售价格',
            'model' => 'Gdoo\Customer\Models\Price::Dialog',
            'url' => 'customer/price/dialog',
        ],
        'customer_region' => [
            'name' => '销售组',
            'model' => 'Gdoo\Customer\Models\Region::Dialog',
            'url' => 'customer/region/dialog',
        ],
        'customer' => [
            'name' => '客户',
            'model' => 'Gdoo\Customer\Models\Customer::Dialog',
            'url' => 'customer/customer/dialog',
        ],
        'customer_contact' => [
            'name' => '客户联系人',
            'model' => 'Gdoo\Customer\Models\Contact::Dialog',
            'url' => 'customer/contact/dialog',
        ],
        'customer_type' => [
            'name'  => '客户类型',
            'model' => 'Gdoo\Customer\Models\CustomerType::Dialog',
            'url'   => 'customer/type/dialog',
        ],
        'customer_tax' => [
            'name' => '客户发票单位',
            'model' => 'Gdoo\Customer\Models\CustomerTax::Dialog',
            'url' => 'customer/tax/dialog',
        ],
        'customer_delivery_address' => [
            'name' => '客户收货地址',
            'model' => 'Gdoo\Customer\Models\DeliveryAddress::Dialog',
            'url' => 'customer/deliveryAddress/dialog',
        ],
        'customer_class' => [
            'name' => '客户分类',
            'model' => 'Gdoo\Customer\Models\CustomerClass::Dialog',
            'url' => 'customer/customerClass/dialog',
        ],
    ],
    'widgets' => [
        'widget_customer_birthday' => [
            'name' => '客户生日',
            'type' => 1,
            'url' => 'customer/widget/birthday',
            'more_url' => 'customer/customer/index',
            'params' => ['permission' => 'dept2', 'date' => 'last_day7'],
        ],
        'info_customer_count' => [
            'name' => '客户',
            'type' => 2,
            'url' => 'customer/widget/customerCount',
            'more_url' => 'customer/customer/index',
            'params' => ['permission' => 'dept2', 'date' => 'month'],
        ],
        'info_customer_contact_count' => [
            'name' => '客户联系人',
            'type' => 2,
            'url' => 'customer/widget/customerContactCount',
            'more_url' => 'customer/contact/index',
            'params' => ['permission' => 'dept2', 'date' => 'month'],
        ],
    ],
    "listens" => [
        'customer' => 'Gdoo\Customer\Hooks\CustomerHook',
        'customer_contact' => 'Gdoo\Customer\Hooks\ContactHook',
        'customer_tax' => 'Gdoo\Customer\Hooks\TaxHook',
        'customer_price' => 'Gdoo\Customer\Hooks\PriceHook',
        'customer_region' => 'Gdoo\Customer\Hooks\RegionHook',
        'customer_delivery_address' => 'Gdoo\Customer\Hooks\DeliveryAddressHook',
        'customer_apply' => 'Gdoo\Customer\Hooks\CustomerApplyHook',
        'customer_task_data' => 'Gdoo\Customer\Hooks\CustomerTaskDataHook',
    ],
    "controllers" => [
        "customer" => [
            "name" => "客户档案",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "show" => [
                    "name" => "查看"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "batchEdit" => [
                    "name" => "批量编辑"
                ],
                "priceEdit" => [
                    "name" => "销售产品价格"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                "import" => [
                    "name" => "导入"
                ],
            ]
        ],
        "type" => [
            "name" => "客户类型",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "customerClass" => [
            "name" => "客户分类",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "region" => [
            "name" => "销售组",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "show" => [
                    "name" => "查看"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "tax" => [
            "name" => "开票单位",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "show" => [
                    "name" => "显示"
                ],
                "audit" => [
                    "name" => "审核"
                ],
                "abort" => [
                    "name" => "弃审"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                "batchEdit" => [
                    "name" => "批量编辑"
                ],
            ]
        ],
        "contact" => [
            "name" => "客户联系人",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "show" => [
                    "name" => "显示"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "task" => [
            "name" => "客户任务",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "show" => [
                    "name" => "查看"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "audit" => [
                    "name" => "审核"
                ],
                "abort" => [
                    "name" => "弃审"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                "progress" => [
                    "name" => "任务进度"
                ],
            ]
        ],
        "regionTask" => [
            "name" => "销售任务",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "show" => [
                    "name" => "查看"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "audit" => [
                    "name" => "审核"
                ],
                "abort" => [
                    "name" => "弃审"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                "progress" => [
                    "name" => "任务进度"
                ],
            ]
        ],
        /*
        "business" => [
            "name" => "客户商机",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "show" => [
                    "name" => "显示"
                ],
                "sms" => [
                    "name" => "短信"
                ],
                "destroy" => [
                    "name" => "删除"
                ]
            ]
        ],
        */
        "price" => [
            "name" => "客户销售价格",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "show" => [
                    "name" => "显示"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "import" => [
                    "name" => "导入"
                ],
                "delete" => [
                    "name" => "删除"
                ],
            ]
        ],
        "customerApply" => [
            "name" => "开户申请",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "show" => [
                    "name" => "显示"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                "audit" => [
                    "name" => "审核"
                ],
                "recall" => [
                    "name" => "撤回"
                ],
                "abort" => [
                    "name" => "弃审"
                ],
            ]
        ],
        "complaint" => [
            "name" => "投诉中心",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "audit" => [
                    "name" => "审核"
                ],
                "recall" => [
                    "name" => "撤回"
                ],
                "abort" => [
                    "name" => "弃审"
                ],
                "show" => [
                    "name" => "显示"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "print" => [
                    "name" => "打印"
                ],
                "delete" => [
                    "name" => "删除"
                ],
            ]
        ],
        "deliveryAddress" => [
            "name" => "收货地址",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "show" => [
                    "name" => "显示"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "report" => [
            "name" => "客户报表",
            "actions" => [
                "accountStatement" => [
                    "name" => "客户对账单"
                ],
            ]
        ]
    ]
];
