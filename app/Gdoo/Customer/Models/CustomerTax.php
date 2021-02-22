<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerTax extends BaseModel
{
    protected $table = 'customer_tax';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'tax.index', 'url' => 'customer/tax/index', 'name' => '发票单位'],
        ]
    ];

    public static $bys = [
        'name' => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];
}
