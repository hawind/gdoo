<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class Contact extends BaseModel
{
    protected $table = 'customer_contact';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'contact.index', 'url' => 'customer/contact/index', 'name' => '客户联系人'],
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

    protected $guarded = ['id', 'user_id'];

    public function user()
    {
        return $this->belongsTo('Gdoo\User\Models\User');
    }

    public function customer()
    {
        return $this->belongsTo('Gdoo\Customer\Models\Customer');
    }

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)->pluck('name', 'id');
    }
}
