<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerType extends BaseModel
{
    protected $table = 'customer_type';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'type.index', 'url' => 'customer/type/index', 'name' => '客户类型'],
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
