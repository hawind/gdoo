<?php 
return [
    "name" => "工作流程",
    "version" => "1.0",
    "description" => "流程管理。",
    'widgets' => [
        'widget_workflow_todo' => [
            'name' => '待办流程',
            'type' => 1,
            'url' => 'workflow/widget/index',
            'more_url' => 'workflow/workflow/index',
        ],
    ],
    'badges' => [
        'workflow_workflow_index' => 'Gdoo\Workflow\Services\WorkflowService::getBadge',
    ],
    "controllers" => [
        "workflow" => [
            "name" => "流程列表",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "view" => [
                    "name" => "查看"
                ],
                "list" => [
                    "name" => "发起"
                ],
                "monitor" => [
                    "name" => "监控"
                ],
                "trash" => [
                    "name" => "回收站",
                ],
                "query" => [
                    "name" => "统计",
                ],
                "add" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "办理"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                "destroy" => [
                    "name" => "销毁"
                ]
            ]
        ],
        "bill" => [
            "name" => "流程管理",
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
                    "name" => "办理"
                ],
                "delete" => [
                    "name" => "删除"
                ],
            ]
        ],
        "widget" => [
            "name" => "流程部件",
            "actions" => [
                "efficiency" => [
                    "name" => "流程效率",
                ]
            ]
        ],
        "monitor" => [
            "name" => "监控流程",
            "actions" => [
                "summary" => [
                    "name" => "汇总"
                ]
            ]
        ],
        "category" => [
            "name" => "流程类别",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "create" => [
                    "name" => "新建"
                ],
                "edit" => [
                    "name" => "办理"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "step" => [
            "name" => "步骤设计",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "view" => [
                    "name" => "查看"
                ],
                "save" => [
                    "name" => "保存"
                ],
                "add" => [
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
        "form" => [
            "name" => "表单设计",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "view" => [
                    "name" => "查看"
                ],
                "count" => [
                    "name" => "新建"
                ]
            ]
        ],
        "design" => [
            "name" => "流程设计",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "process" => [
                    "name" => "编辑"
                ],
                "add" => [
                    "name" => "新建"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],"template" => [
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
    ]
];
