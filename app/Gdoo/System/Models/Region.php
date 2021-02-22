<?php namespace Gdoo\System\Models;

use Gdoo\Index\Models\BaseModel;

class Region extends BaseModel
{
    protected $table = 'region';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'region', 'url' => 'system/region/index', 'name' => '城市档案'],
        ]
    ];

    static public $bys = [
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
        return $q->whereIn('id', $value)->pluck('name', 'id');
    }
}