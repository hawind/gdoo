<?php namespace Gdoo\CustomerCost\Models;

use Gdoo\Index\Models\BaseModel;

class Cost extends BaseModel
{
    protected $table = 'customer_cost';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'cost.index', 'url' => 'customerCost/cost/index', 'name' => '费用管理'],
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

    public function customer()
    {
        return $this->belongsTo('Gdoo\Customer\Models\Customer');
    }
}
