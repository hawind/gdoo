<?php 
return [
    "name" => "讨论",
    "version" => "1.0",
    "description" => "讨论管理",
    "controllers" => [
        "category" => [
            "name" => "板块",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "view" => [
                    "name" => "查看"
                ],
                "add" => [
                    "name" => "新建"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "post" => [
            "name" => "帖子",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "category" => [
                    "name" => "类别"
                ],
                "reply" => [
                    "name" => "回复"
                ],
                "view" => [
                    "name" => "查看"
                ],
                "add" => [
                    "name" => "新建"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ]
    ]
];
