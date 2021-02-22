<?php 
return [
    "name" => "客户费用",
    "version" => "1.0",
    "description" => "客户费用管理。",
    "listens" => [
        'customer_cost' => 'Gdoo\CustomerCost\Hooks\CostHook',
        'customer_cost_data' => 'Gdoo\CustomerCost\Hooks\CostDataHook',
    ],
    'dialogs' => [
        'customer_cost' => [
            'name' => '客户费用列表',
            'model' => 'Gdoo\Market\Models\Cost::Dialog',
            'url' => 'customerCost/cost/dialog',
        ],
        'customer_cost_data' => [
            'name' => '客户费用明细表',
            'model' => 'Gdoo\Market\Models\CostData::Dialog',
            'url' => 'customerCost/cost/dialog',
        ],
        'customer_cost_category' => [
            'name' => '费用类别',
            'model' => 'Gdoo\Market\Models\Category::Dialog',
            'url' => 'customerCost/category/dialog',
        ],
    ],
    "controllers" => [
        "cost" => [
            "name" => "客户费用",
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
                "close" => [
                    "name" => "关闭"
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
                "print" => [
                    "name" => "打印"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                'batchEdit' => [
                    "name" => "批量编辑",
                ],
            ]
        ],
        "adjust" => [
            "name" => "费用调整单",
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
                "print" => [
                    "name" => "打印"
                ],
                "delete" => [
                    "name" => "删除"
                ],
            ]
        ],
        "compen" => [
            "name" => "合同补损",
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
                "print" => [
                    "name" => "打印"
                ],
                "delete" => [
                    "name" => "删除"
                ],
            ]
        ],
        "rebate" => [
            "name" => "合同返利",
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
                "print" => [
                    "name" => "打印"
                ],
                "delete" => [
                    "name" => "删除"
                ],
            ]
        ],
        "category" => [
            "name" => "费用类别",
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
                ],
            ]
        ],
        "report" => [
            "name" => "客户费用报表",
            "actions" => [
                "saleOrderDetail" => [
                    "name" => "费用使用明细表"
                ]
            ]
        ]
    ],
];
