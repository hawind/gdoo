<?php namespace Gdoo\Stock\Models;

use Gdoo\Index\Models\BaseModel;

class StockCategory extends BaseModel
{
    protected $table = 'stock_type';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'category', 'url' => 'stock/category/index', 'name' => '库存类别'],
        ]
    ];

    public static $bys = [
        'name'  => 'by',
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
