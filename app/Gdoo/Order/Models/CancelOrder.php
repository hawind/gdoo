<?php namespace Gdoo\Order\Models;

use Gdoo\Index\Models\BaseModel;

class CancelOrder extends BaseModel
{
    protected $table = 'cancel_order';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['url' => 'order/cancel/index', 'name' => '客户退货', 'value' => 'index'],
        ]
    ];

    public static $todo_tabs = [
        'name' => 'tab',
        'items' => [
            ['url' => 'order/cancel/todo', 'name' => '待办中', 'value' => 'todo'],
            ['url' => 'order/cancel/todo', 'name' => '已办理', 'value' => 'trans'],
            ['url' => 'order/cancel/todo', 'name' => '已结束', 'value' => 'done'],
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
    
    public function datas()
    {
        return $this->hasMany('Gdoo\Order\Models\CustomerOrderData');
    }
}
