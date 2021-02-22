<?php namespace Gdoo\Purchase\Models;

use Gdoo\Index\Models\BaseModel;

class Order extends BaseModel
{
    protected $table = 'purchase_order';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'order.index', 'url' => 'purchase/order/index', 'name' => '采购订单'],
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
