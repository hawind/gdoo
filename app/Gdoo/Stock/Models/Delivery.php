<?php namespace Gdoo\Stock\Models;

use DB;
use Gdoo\Index\Models\BaseModel;

class Delivery extends BaseModel
{
    protected $table = 'stock_delivery';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'delivery', 'url' => 'stock/delivery/index', 'name' => '发货单'],
        ]
    ];

    public static $tabs2 = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'delivery', 'url' => 'stock/delivery/detail', 'name' => '发货明细'],
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

    public function warehouse($query)
    {
        return $this->belongsTo('Gdoo\Stock\Models\Warehouse');
    }

}
