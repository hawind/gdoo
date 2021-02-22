<?php namespace Gdoo\Stock\Models;

use Gdoo\Index\Models\BaseModel;

class Warehouse extends BaseModel
{
    protected $table = 'warehouse';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'stock', 'url' => 'stock/warehouse/index', 'name' => '仓库档案'],
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

    public function user()
    {
        return $this->belongsTo('Gdoo\User\Models\User');
    }

}
