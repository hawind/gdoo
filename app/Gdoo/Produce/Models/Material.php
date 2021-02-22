<?php namespace Gdoo\Produce\Models;

use Gdoo\Index\Models\BaseModel;

class Material extends BaseModel
{
    protected $table = 'product_material';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'material.index', 'url' => 'produce/material/index', 'name' => '原辅料档案'],
        ]
    ];

    public static $bys = [
        'name' => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];
}
