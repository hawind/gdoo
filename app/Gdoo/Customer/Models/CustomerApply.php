<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerApply extends BaseModel
{
    protected $table = 'customer_apply';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'customerApply.index', 'url' => 'customer/customerApply/index', 'name' => '开户申请'],
        ]
    ];

    public static $bys = [
        'name' => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'todo', 'name' => '待审'],
            ['value' => 'end', 'name' => '已审'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];
}
