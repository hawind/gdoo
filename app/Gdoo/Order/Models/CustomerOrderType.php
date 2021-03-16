<?php namespace Gdoo\Order\Models;

use Gdoo\Index\Models\BaseModel;

class CustomerOrderType extends BaseModel
{
    protected $table = 'customer_order_type';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'type.index', 'url' => 'order/type/index', 'name' => '订单类型'],
        ]
    ];

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)->pluck('name', 'id');
    }
}
