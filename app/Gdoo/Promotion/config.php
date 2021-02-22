<?php 
return [
    "name" => "促销管理",
    "version" => "1.0",
    "description" => "促销管理。",
    "listens" => [
        'promotion' => 'Gdoo\Promotion\Hooks\PromotionHook',
        'promotion_review' => 'Gdoo\Promotion\Hooks\ReviewHook',
    ],
    'dialogs' => [
        'promotion' => [
            'name' => '促销申请',
            'model' => 'Gdoo\Promotion\Models\Promotion::Dialog',
            'url' => 'promotion/promotion/dialog',
        ],
    ],
    'badges' => [
        'promotion_promotion_index' => 'Gdoo\Promotion\Services\PromotionService::getBadge',
    ],
    "controllers" => [
        "promotion" => [
            "name" => "促销管理",
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
            "name" => "促销核销",
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
        "material" => [
            "name" => "促销素材",
            "actions" => [
                "index" => [
                    "name" => "列表",
                ],
                "show" => [
                    "name" => "显示"
                ],
                "audit" => [
                    "name" => "审核"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
    ]
];
