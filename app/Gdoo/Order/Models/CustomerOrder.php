<?php namespace Gdoo\Order\Models;

use DB;
use Gdoo\Index\Models\BaseModel;

use Gdoo\CustomerCost\Models\CostData;

class CustomerOrder extends BaseModel
{
    protected $table = 'customer_order';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['url' => 'order/order/index', 'name' => '销售订单', 'value' => 'order.index'],
        ]
    ];

    public static $tabs2 = [
        'name' => 'tab',
        'items' => [
            ['url' => 'order/order/detail', 'name' => '销售订单明细', 'value' => 'order.detail'],
        ]
    ];

    public static $tabs3 = [
        'name' => 'tab',
        'items' => [
            ['url' => 'order/order/delivery', 'name' => '发货计划', 'value' => 'delivery'],
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
    
    public function promotions()
    {
        return $this->hasMany('Gdoo\Promotion\Models\Promotion', 'customer_id', 'customer_id');
    }

    public function approachs()
    {
        return $this->hasMany('Gdoo\Approach\Models\Approach', 'customer_id', 'customer_id');
    }

    public function datas()
    {
        return $this->hasMany('Gdoo\Order\Models\CustomerOrderData');
    }

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)->pluck('sn', 'id');
    }
}
