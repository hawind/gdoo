<?php 
return [
    "name" => "销售订单",
    "version" => "1.0",
    "description" => "订单管理,销售支持,生产计划,订单类型,订单发货。",
    'dialogs' => [
        'customer_order' => [
            'name' => '客户订单',
            'model' => 'Gdoo\Order\Models\CustomerOrder::Dialog',
            'url' => 'order/order/dialog',
        ],
        'customer_order_type' => [
            'name' => '客户订单类型',
            'model' => 'Gdoo\Order\Models\CustomerOrderType::Dialog',
            'url' => 'order/type/dialog',
        ],
        'logistics' => [
            'name' => '物流供应商',
            'model' => 'Gdoo\Order\Models\Logistics::Dialog',
            'url' => 'order/logistics/dialog',
        ],
    ],
    "listens" => [
        'customer_order' => 'Gdoo\Order\Hooks\OrderHook',
        'customer_order_data' => 'Gdoo\Order\Hooks\OrderDataHook',
    ],
    'badges' => [
        'order_order_index' => 'Gdoo\Order\Services\OrderService::getBadge',
    ],
    'widgets' => [
        'widget_order_index' => [
            'name' => '销售订单统计',
            'type' => 1,
            'url' => 'order/widget/index',
            'more_url' => 'order/order/index',
        ],
        'widget_order_goods' => [
            'name' => '明日预计到货',
            'type' => 1,
            'url' => 'order/widget/goods',
            'more_url' => 'order/order/delivery',
        ],
        'info_order_count' => [
            'name' => '销售订单(元)',
            'type' => 2,
            'url' => 'order/widget/orderCount',
            'more_url' => 'order/order/index',
            'params' => ['permission' => 'dept2', 'date' => 'month'],
        ],
    ],
    "controllers" => [
        "order" => [
            "name" => "客户订单",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "detail" => [
                    "name" => "明细列表"
                ],
                'delivery' => [
                    "name" => "发货计划"
                ],
                'deliveryPlan' => [
                    "name" => "修改预计发货日期"
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
                'logisticsPlan' => [
                    "name" => "修改物流信息",
                ],
                'deliveryEdit' => [
                    "name" => "修改运费支付方式",
                ],
                'closeRow' => [
                    "name" => "关闭行(恢复)",
                ],
                'closeAllRow' => [
                    "name" => "关闭所有行(恢复)",
                ],
                'batchEdit' => [
                    "name" => "批量编辑",
                ],
            ]
        ],
        "cancel" => [
            "name" => "订单退货",
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
                ]
            ]
        ],
        "sampleApply" => [
            "name" => "样品申请",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "detail" => [
                    "name" => "明细列表"
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
                "close" => [
                    "name" => "关闭"
                ],
                "print" => [
                    "name" => "打印"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "plan" => [
            "name" => "生产计划",
            "actions" => [
                "index" => [
                    "name" => "生产计划总表"
                ],
                "produce" => [
                    "name" => "生产计划(营销)"
                ],
                "export_sale" => [
                    "name" => "外销生产进度表"
                ],
                "produce_save" => [
                    "name" => "保存生产计划"
                ],
                "produce_submit" => [
                    "name" => "提交生产计划"
                ]
            ]
        ],
        "type" => [
            "name" => "订单类型",
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
        "logistics" => [
            "name" => "物流公司",
            "actions" => [
                "index" => [
                    "name" => "列表",
                ],
                "create" => [
                    "name" => "创建",
                ],
                "edit" => [
                    "name" => "编辑",
                ],
                "show" => [
                    "name" => "显示",
                ],
                "delete" => [
                    "name" => "删除",
                ],
            ]
        ],
        "report" => [
            "name" => "客户销售报表",
            "actions" => [
                "index" => [
                    "name" => "销售曲线表"
                ],
                "category" => [
                    "name" => "销售品类汇总表"
                ],
                "single" => [
                    "name" => "销售单品汇总表"
                ],
                "city" => [
                    "name" => "区域销售品类表"
                ],
                "client" => [
                    "name" => "销售单品客户表"
                ],
                "ranking" => [
                    "name" => "销售排名表"
                ],
                "newclient" => [
                    "name" => "年度新客户表"
                ],
                "stockmonth" => [
                    "name" => "三个月未进货客户报表"
                ],
            ]
        ]
    ]
];
