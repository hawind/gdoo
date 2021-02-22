<?php namespace Gdoo\Order\Models;

use Gdoo\Index\Models\BaseModel;

class Logistics extends BaseModel
{
    protected $table = 'logistics';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'logistics.index', 'url' => 'order/logistics/index', 'name' => '物流公司'],
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
