<?php namespace Gdoo\System\Models;

use Gdoo\Index\Models\BaseModel;

class Region extends BaseModel
{
    protected $table = 'region';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'region', 'url' => 'system/region/index', 'name' => '城市档案'],
        ]
    ];

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)->pluck('name', 'id');
    }
}