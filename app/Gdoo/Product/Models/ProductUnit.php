<?php namespace Gdoo\Product\Models;

use Gdoo\Index\Models\BaseModel;

class ProductUnit extends BaseModel
{
    protected $table = 'product_unit';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'unit.index', 'url' => 'product/unit/index', 'name' => '计量单位'],
        ]
    ];

    public static $bys = [
        'name' => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'enabled', 'name' => '启用'],
            ['value' => 'disabled', 'name' => '禁用'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];
}
