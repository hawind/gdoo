<?php 
return [
    "name" => "库存管理",
    "version" => "1.0",
    "description" => "产品列表,产品类别,库存类型,仓库类别,库存管理,仓库列表。",
    "listens" => [
        'stock_delivery' => 'Gdoo\Stock\Hooks\DeliveryHook',
        'stock_delivery_data' => 'Gdoo\Stock\Hooks\DeliveryDataHook',
        'stock_record11' => 'Gdoo\Stock\Hooks\Record11Hook',
        
        'stock_record10' => 'Gdoo\Stock\Hooks\Record10Hook',
        'stock_record10_data' => 'Gdoo\Stock\Hooks\Record10DataHook',

        'stock_record09' => 'Gdoo\Stock\Hooks\Record09Hook',
        'stock_record08' => 'Gdoo\Stock\Hooks\Record08Hook',
        'stock_record01' => 'Gdoo\Stock\Hooks\Record01Hook',
        'stock_direct' => 'Gdoo\Stock\Hooks\DirectHook',
        'stock_cancel' => 'Gdoo\Stock\Hooks\CancelHook',
        'stock_allocation' => 'Gdoo\Stock\Hooks\AllocationHook',
    ],
    'badges' => [
        'stock_delivery_index' => 'Gdoo\Stock\Services\DeliveryService::getBadge',
    ],
    'dialogs' => [
        'warehouse' => [
            'name' => '仓库',
            'model' => 'Gdoo\Stock\Models\Warehouse::Dialog',
            'url' => 'stock/warehouse/dialog',
        ],
        'location_batch' => [
            'name' => '库存数量',
            'model' => 'Gdoo\Stock\Models\WarehouseLocation::Dialog',
            'url' => 'stock/location/dialog2',
        ],
        'warehouse_location' => [
            'name' => '仓库货位',
            'model' => 'Gdoo\Stock\Models\WarehouseLocation::Dialog',
            'url' => 'stock/location/dialog',
        ],
    ],
    "controllers" => [
        "delivery" => [
            "name" => "发货单",
            "actions" => [
                "index" => [
                    "name" => "列表",
                ],
                "detail" => [
                    "name" => "明细列表",
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
                "recall" => [
                    "name" => "撤回"
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
                "batchEdit" => [
                    "name" => "批量编辑"
                ],
            ]
        ],
        "direct" => [
            "name" => "发货单(直营)",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
                "recall" => [
                    "name" => "撤回"
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
        "allocation" => [
            "name" => "产成品调拨单",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
                "recall" => [
                    "name" => "撤回"
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
        "cancel" => [
            "name" => "退货申请",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
                "recall" => [
                    "name" => "撤回"
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
                "batchEdit" => [
                    "name" => "批量编辑"
                ],
            ]
        ],
        "record01" => [
            "name" => "采购入库单",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
                "recall" => [
                    "name" => "撤回"
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
        "record10" => [
            "name" => "产成品入库单",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
                "recall" => [
                    "name" => "撤回"
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
        "record08" => [
            "name" => "其他入库单",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
                "recall" => [
                    "name" => "撤回"
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
        "record09" => [
            "name" => "其他出库单",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
                "recall" => [
                    "name" => "撤回"
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
        "record11" => [
            "name" => "原材料出库单",
            "actions" => [
                "index" => [
                    "name" => "列表",
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
                "recall" => [
                    "name" => "撤回"
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
        "warehouse" => [
            "name" => "仓库档案",
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
        "location" => [
            "name" => "仓库货位",
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
        "type" => [
            "name" => "库存类型",
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
        "category" => [
            "name" => "库存类别",
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
        "report" => [
            "name" => "库存报表",
            "actions" => [
                "stockDetail" => [
                    "name" => "库存明细表"
                ],
                "stockTotal" => [
                    "name" => "库存汇总表"
                ],
                "stockInOut" => [
                    "name" => "进销存汇总表"
                ],
            ]
        ]
    ]
];
