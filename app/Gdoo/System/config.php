<?php 
return [
    "name" => "系统配置",
    "version" => "1.0",
    "description" => "系统模块",
    'dialogs' => [
        'region' => [
            'name' => '行政区域',
            'model' => 'Gdoo\System\Models\Region::Dialog',
            'url' => 'system/region/dialog',
        ],
    ],
    "listens" => [
        'region' => 'Gdoo\System\Hooks\RegionHook',
    ],
    "controllers" => [
        "setting" => [
            "name" => "基础设置",
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
                "store" => [
                    "name" => "存储"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "mail" => [
            "name" => "邮件管理",
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
                "store" => [
                    "name" => "存储"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "region" => [
            "name" => "城市档案",
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
        "sms" => [
            "name" => "短信管理",
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
                "store" => [
                    "name" => "存储"
                ],
                "delete" => [
                    "name" => "删除"
                ],
            ]
        ],
        "smsLog" => [
            "name" => "邮件记录",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "log" => [
            "name" => "系统记录",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "menu" => [
            "name" => "菜单管理",
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
        "widget" => [
            "name" => "部件管理",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "cron" => [
            "name" => "定时任务",
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
        "option" => [
            "name" => "枚举管理",
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
                "store" => [
                    "name" => "存储"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ]
    ]
];
