<?php namespace Gdoo\Stock\Models;

use DB;
use Gdoo\Index\Models\BaseModel;

class Record01 extends BaseModel
{
    protected $table = 'stock_record01';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'record01', 'url' => 'stock/record01/index', 'name' => '采购入库单'],
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

    public function warehouse($query)
    {
        return $this->belongsTo('Gdoo\Stock\Models\Warehouse');
    }

}
