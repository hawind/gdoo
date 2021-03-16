<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerRegionTask extends BaseModel
{
    protected $table = 'customer_region_task';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'regionTask.index', 'url' => 'customer/regionTask/index', 'name' => '区域任务'],
        ]
    ];
}
