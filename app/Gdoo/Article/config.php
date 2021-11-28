<?php 
return [
    "name" => "公告",
    "version" => "1.0",
    "description" => "公告。",
    "listens" => [
        'article' => 'Gdoo\Article\Hooks\ArticleHook',
    ],
    'widgets' => [
        'widget_article_index' => [
            'name' => '新闻公告',
            'type' => 1,
            'url' => 'article/widget/index',
            'more_url' => 'article/article/index',
        ],
    ],
    'badges' => [
        'article_article_index' => 'Gdoo\Article\Services\ArticleService::getBadge',
    ],
    'menus' => [
        ['name' => '资讯', 'id' => 'article'],
        ['name' => '公告列表', 'id' => 'article_article_index', 'parent' => 'article', 'url' => 'article/article/index', 'badge' => 'article_article_index'],
    ],
    "controllers" => [
        "article" => [
            "name" => "公告",
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
                    "name" => "查看"
                ],
                "reader" => [
                    "name" => "阅读记录"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ]
    ]
];
