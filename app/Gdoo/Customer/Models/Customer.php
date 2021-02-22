<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class Customer extends BaseModel
{
    protected $table = 'customer';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'customer.index', 'url' => 'customer/customer/index', 'name' => '客户档案'],
        ]
    ];

    public static $bys = [
        'name'  => 'by',
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

    public function user()
    {
        return $this->belongsTo(\Gdoo\User\Models\User::class);
    }

    public function region()
    {
        return $this->belongsTo(\Gdoo\Customer\Models\Region::class);
    }
    
    public function contacts()
    {
        return $this->hasMany(\Gdoo\Customer\Models\Contact::class);
    }

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)
        ->pluck('name', 'id');
    }
}
