<?php namespace Gdoo\Order\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerDelivery extends BaseModel
{
    protected $table = 'customer_delivery';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['url' => 'order/delivery/index', 'name' => '客户发货', 'value' => 'index'],
        ]
    ];

    public static $todo_tabs = [
        'name' => 'tab',
        'items' => [
            ['url' => 'order/delivery/todo', 'name' => '待办中', 'value' => 'todo'],
            ['url' => 'order/delivery/todo', 'name' => '已办理', 'value' => 'trans'],
            ['url' => 'order/delivery/todo', 'name' => '已结束', 'value' => 'done'],
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
}
