<?php namespace Gdoo\CustomerCost\Models;

use Gdoo\Index\Models\BaseModel;

class Category extends BaseModel
{
    protected $table = 'customer_cost_category';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'category.index', 'url' => 'customerCost/category/index', 'name' => '费用类别'],
        ]
    ];
}
