<?php namespace Gdoo\Stock\Models;

use DB;
use Gdoo\Index\Models\BaseModel;

class Record11 extends BaseModel
{
    protected $table = 'stock_record11';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'record11', 'url' => 'stock/record11/index', 'name' => '原材料出库单'],
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

    public function warehouse()
    {
        return $this->belongsTo('Gdoo\Stock\Models\Warehouse');
    }

}
