<?php namespace Gdoo\Purchase\Models;

use Gdoo\Index\Models\BaseModel;

class Supplier extends BaseModel
{
    protected $table = 'supplier';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'supplier.index', 'url' => 'purchase/supplier/index', 'name' => '供应商档案'],
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

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)
        ->pluck('name', 'id');
    }
}
