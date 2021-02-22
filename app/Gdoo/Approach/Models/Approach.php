<?php namespace Gdoo\Approach\Models;

use Gdoo\Index\Models\BaseModel;

class Approach extends BaseModel
{
    protected $table = 'approach';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'approach', 'url' => 'approach/approach/index', 'name' => '进店列表'],
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

    public function customer()
    {
        return $this->belongsTo('Gdoo\Customer\Models\Customer');
    }

    public function datas()
    {
        return $this->hasMany('Gdoo\Promotion\Models\PromotionData');
    }

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)->pluck('sn', 'id');
    }
}
