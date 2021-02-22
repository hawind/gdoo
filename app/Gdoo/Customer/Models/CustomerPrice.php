<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerPrice extends BaseModel
{
    protected $table = 'customer_price';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'price.index', 'url' => 'customer/price/index', 'name' => '客户销售价格'],
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
