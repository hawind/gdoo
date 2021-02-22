<?php namespace Gdoo\Stock\Models;

use Gdoo\Index\Models\BaseModel;

class WarehouseLocation extends BaseModel
{
    protected $table = 'warehouse_location';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'location', 'url' => 'stock/location/index', 'name' => '仓库货位'],
        ]
    ];

    public static $bys = [
        'name'  => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];
}
