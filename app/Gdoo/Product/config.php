<?php 
return [
    "name" => "产品管理",
    "version" => "1.0",
    "description" => "产品列表,产品类别,库存类型,仓库类别,库存管理,仓库列表。",
    "listens" => [
        'product_category' => 'Gdoo\Product\Hooks\CategoryHook',
    ],
    'dialogs' => [
        'product' => [
            'name' => '产品',
            'model' => 'Gdoo\Product\Models\Product::Dialog',
            'url' => 'product/product/dialog',
        ],
        'product_category' => [
            'name' => '产品类别',
            'model' => 'Gdoo\Product\Models\ProductCategory::Dialog',
            'url' => 'product/category/dialog',
        ],
        'product_unit' => [
            'name' => '计量单位',
            'model' => 'Gdoo\Product\Models\ProductUnit::Dialog',
            'url' => 'product/unit/dialog',
        ],
    ],
    "controllers" => [
        "product" => [
            "name" => "产品列表",
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
                "import" => [
                    "name" => "导入"
                ],
                "delete" => [
                    "name" => "删除"
                ]
            ]
        ],
        "unit" => [
            "name" => "计量单位",
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
                ],
            ]
        ],
        "material" => [
            "name" => "原材料清单",
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
                ],
            ]
        ],
        "category" => [
            "name" => "产品类别",
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
                ],
            ]
        ],
    ]
];
