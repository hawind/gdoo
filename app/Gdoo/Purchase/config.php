<?php 
return [
    "name" => "采购管理",
    "version" => "1.0",
    "description" => "",
    'dialogs' => [
        'supplier' => [
            'name' => '供应商',
            'model' => 'Gdoo\Purchase\Models\Supplier::Dialog',
            'url' => 'purchase/supplier/dialog',
        ],
    ],
    "listens" => [
    ],
    "controllers" => [
        "order" => [
            "name" => "采购订单",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
                "audit" => [
                    "name" => "审核"
                ],
                "recall" => [
                    "name" => "撤回"
                ],
                "abort" => [
                    "name" => "弃审"
                ],
                "delete" => [
                    "name" => "删除"
                ],
            ]
        ],
        "supplier" => [
            "name" => "供应商档案",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
            ]
        ],
    ]
];
