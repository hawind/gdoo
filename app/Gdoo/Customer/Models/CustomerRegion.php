<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerRegion extends BaseModel
{
    protected $table = 'customer_region';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'region.index', 'url' => 'customer/region/index', 'name' => '销售组'],
        ]
    ];
}
