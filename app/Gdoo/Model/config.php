<?php 
return [
    "name" => "模型管理",
    "version" => "1.0",
    "description" => "模型管理",
    "icons" => [
        16 => "images/16.png",
        48 => "images/48.png",
        128 => "images/128.png"
    ],
    'widgets' => [
        'widget_model_todo' => [
            'name' => '待办事项',
            'type' => 1,
            'url' => 'model/todo/widget',
            'more_url' => 'model/todo/index',
        ],
    ],
    "controllers" => [
        "bill" => [
            "name" => "单据",
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
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "model" => [
            "name" => "模型",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "新建"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "template" => [
            "name" => "视图",
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
        "field" => [
            "name" => "字段",
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
                "type" => [
                    "name" => "类型"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "step" => [
            "name" => "步骤",
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
                "save" => [
                    "name" => "保存"
                ],
                "condition" => [
                    "name" => "条件"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                "move" => [
                    "name" => "移交"
                ]
            ]
        ],
        "module" => [
            "name" => "模块",
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
                "save" => [
                    "name" => "保存"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ]
    ]
];
