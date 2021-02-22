<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerComplaint extends BaseModel
{
    protected $table = 'customer_complaint';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'complaint.index', 'url' => 'customer/complaint/index', 'name' => '投诉中心'],
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
