<?php 
return [
    "name" => "进店管理",
    "version" => "1.0",
    "description" => "条码进店。",
    "listens" => [
        'approach' => 'Gdoo\Approach\Hooks\ApproachHook',
        'approach_review' => 'Gdoo\Approach\Hooks\ReviewHook',
    ],
    'dialogs' => [
        'approach' => [
            'name' => '进店申请',
            'model' => 'Gdoo\Approach\Models\Approach::Dialog',
            'url' => 'approach/approach/dialog',
        ],
        'approach_market' => [
            'name' => '进店超市',
            'model' => 'Gdoo\Approach\Models\ApproachMarket::Dialog',
            'url' => 'approach/market/dialog',
        ],
    ],
    'badges' => [
        'approach_approach_index' => 'Gdoo\Approach\Services\ApproachService::getBadge',
    ],
    "controllers" => [
        "approach" => [
            "name" => "进店申请",
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
                "close" => [
                    "name" => "关闭"
                ],
                "batchEdit" => [
                    "name" => "批量编辑"
                ],
            ]
        ],
        "review" => [
            "name" => "进店核销",
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
        "market" => [
            "name" => "进店超市",
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
                ],
            ]
        ]
    ]
];
