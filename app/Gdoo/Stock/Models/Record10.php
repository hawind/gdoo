<?php namespace Gdoo\Stock\Models;

use DB;
use Gdoo\Index\Models\BaseModel;

class Record10 extends BaseModel
{
    protected $table = 'stock_record10';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'record10', 'url' => 'stock/record10/index', 'name' => '产成品入库单'],
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

    public function warehouse($query)
    {
        return $this->belongsTo('Gdoo\Stock\Models\Warehouse');
    }

}
