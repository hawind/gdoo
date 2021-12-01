<?php 
return [
    "name" => "组织架构",
    "version" => "1.0",
    "description" => "账户、职位、角色权限、部门管理。",
    "listens" => [
        'user' => 'Gdoo\User\Hooks\UserHook',
        'role' => 'Gdoo\User\Hooks\RoleHook',
        'department' => 'Gdoo\User\Hooks\DepartmentHook',
    ],
    'dialogs' => [
        'department' => [
            'name'  => '部门',
            'model' => 'Gdoo\User\Models\Department::Dialog',
            'url' => 'user/department/dialog',
        ],
        'role' => [
            'name' => '角色',
            'model' => 'Gdoo\User\Models\Role::Dialog',
            'url' => 'user/role/dialog',
        ],
        'post' => [
            'name' => '岗位',
            'model' => 'Gdoo\User\Models\UserPost::Dialog',
            'url' => 'user/post/dialog',
        ],
        'user' => [
            'name' => '用户',
            'model' => 'Gdoo\User\Models\User::Dialog',
            'url' => 'user/user/dialog',
        ],
    ],
    'widgets' => [
        'info_user_count' => [
            'name' => '用户',
            'type' => 2,
            'url' => 'user/widget/userCount',
            'more_url' => 'user/user/index',
            'params' => ['permission' => 'dept2', 'date' => 'month'],
        ],
    ],
    "controllers" => [
        "user" => [
            "name" => "用户",
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
                "import" => [
                    "name" => "导入"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                "secret" => [
                    "name" => "密钥"
                ]
            ]
        ],
        "department" => [
            "name" => "部门",
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
        "role" => [
            "name" => "角色",
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
                "config" => [
                    "name" => "权限"
                ],
                "delete" => [
                    "name" => "删除"
                ],
            ]
        ],
        "group" => [
            "name" => "用户组",
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
        "post" => [
            "name" => "用户职位",
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
        ]
    ]
];
