<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerClass extends BaseModel
{
    protected $table = 'customer_class';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'customerClass.index', 'url' => 'customer/customerClass/index', 'name' => '客户分类'],
        ]
    ];

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)
        ->pluck('name', 'id');
    }
}
