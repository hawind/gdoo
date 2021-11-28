<?php 
return [
    "name" => "日程管理",
    "version" => "1.0",
    "description" => "日历日程安排，下属日历查看，支持流行的caldav协议。",
    'menus' => [
        ['name' => '工作', 'id' => 'work'],
        ['name' => '日程管理', 'id' => 'calendar_calendar_index', 'parent' => 'work', 'url' => 'calendar/calendar/index'],
    ],
    'widgets' => [
        'widget_calendar_index' => [
            'name' => '日程管理',
            'type' => 1,
            'url' => 'calendar/widget/index',
            'more_url' => 'calendar/calendar/index',
        ],
    ],
    "controllers" => [
        "calendar" => [
            "name" => "日历",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "calendar" => [
                    "name" => "读取日历"
                ],
                "active" => [
                    "name" => "活动日历"
                ],
                "view" => [
                    "name" => "查看"
                ],
                "add" => [
                    "name" => "新建"
                ],
                "delete" => [
                    "name" => "删除"
                ],
                "help" => [
                    "name" => "帮助"
                ]
            ]
        ],
        "event" => [
            "name" => "事件",
            "actions" => [
                "index" => [
                    "name" => "列表"
                ],
                "resize" => [
                    "name" => "调整事件"
                ],
                "move" => [
                    "name" => "移动事件"
                ],
                "view" => [
                    "name" => "查看"
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
        ]
    ]
];
