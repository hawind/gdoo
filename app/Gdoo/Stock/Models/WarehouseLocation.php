<?php namespace Gdoo\Stock\Models;

use Gdoo\Index\Models\BaseModel;

class WarehouseLocation extends BaseModel
{
    protected $table = 'warehouse_location';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'location', 'url' => 'stock/location/index', 'name' => '仓库货位'],
        ]
    ];
}
