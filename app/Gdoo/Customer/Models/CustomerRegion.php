<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerRegion extends BaseModel
{
    protected $table = 'customer_region';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'region.index', 'url' => 'customer/region/index', 'name' => '销售组'],
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
