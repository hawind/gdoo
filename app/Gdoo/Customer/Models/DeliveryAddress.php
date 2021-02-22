<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class DeliveryAddress extends BaseModel
{
    protected $table = 'customer_delivery_address';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'deliveryAddress.index', 'url' => 'customer/deliveryAddress/index', 'name' => '收货地址'],
        ]
    ];

    public static $bys = [
        'name' => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'enabled', 'name' => '启用'],
            ['value' => 'disabled', 'name' => '禁用'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];

    protected $guarded = ['id', 'user_id'];

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)->pluck('name', 'id');
    }
}
