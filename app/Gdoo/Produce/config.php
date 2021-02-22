<?php 
return [
    "name" => "生产管理",
    "version" => "1.0",
    "description" => "原辅料档案。",
    'dialogs' => [
    ],
    "listens" => [
        'produce_plan_data' => 'Gdoo\Produce\Hooks\PlanDataHook',
    ],
    "controllers" => [
        "material" => [
            "name" => "原辅料档案",
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
                "plan" => [
                    "name" => "用料计划"
                ],
            ]
        ],
        "plan" => [
            "name" => "生产计划单",
            "actions" => [
                "index" => [
                    "name" => "列表",
                ],
                "count" => [
                    "name" => "统计",
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
                "planExport" => [
                    "name" => "生产计划导出"
                ]
            ]
        ],
    ]
];
