<?php namespace Gdoo\CustomerCost\Models;

use Gdoo\Index\Models\BaseModel;

class Category extends BaseModel
{
    protected $table = 'customer_cost_category';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'category.index', 'url' => 'customerCost/category/index', 'name' => '费用类别'],
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
