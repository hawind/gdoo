<?php 
return [
    "name" => "文件管理",
    "version" => "1.0",
    "description" => "文件查看下载，供大家下载资料的通用功能。",
    "controllers" => [
        "certificate" => [
            "name" => "常规证照",
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
                "download" => [
                    "name" => "下载"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ], 
        "inspectReport" => [
            "name" => "出厂检验报告",
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
                "download" => [
                    "name" => "下载"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ]
    ]
];
