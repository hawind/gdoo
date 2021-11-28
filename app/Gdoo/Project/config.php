<?php 
return [
    "name" => "项目管理",
    "version" => "1.0",
    "description" => "项目管理。",
    'widgets' => [
        'info_project_task' => [
            'name' => '项目任务',
            'type' => 2,
            'url' => 'project/widget/info',
            'more_url' => 'project/project/index',
            'params' => ['permission' => 'dept2', 'date' => 'month'],
        ],
    ],
    'badges' => [
        'project_project_index' => 'Gdoo\Project\Services\TaskService::getBadge',
    ],
    'menus' => [
        ['name' => '工作', 'id' => 'work'],
        ['name' => '项目管理', 'id' => 'project_project', 'parent' => 'work'],
        ['name' => '项目列表', 'id' => 'project_project_index', 'parent' => 'project_project', 'url' => 'project/project/index'],
    ],
    "controllers" => [
        "project" => [
            "name" => "项目",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "add" => [
                    "name" => "添加",
                ],
                "show" => [
                    "name" => "显示"
                ],
                "edit" => [
                    "name" => "编辑",
                ],
                "delete" => [
                    "name" => "删除",
                ]
            ]
        ],
        "task" => [
            "name" => "任务",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "add" => [
                    "name" => "添加"
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
        "comment" => [
            "name" => "评论",
            "actions" => [
                "add" => [
                    "name" => "添加"
                ],
                "edit" => [
                    "name" => "编辑"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ]
    ]
];
