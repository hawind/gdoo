<?php namespace Gdoo\Order\Models;

use Gdoo\Index\Models\BaseModel;

class SampleApply extends BaseModel
{
    protected $table = 'sample_apply';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['url' => 'order/sampleApply/index', 'name' => '样品申请', 'value' => 'index'],
        ]
    ];

    public static $tabs2 = [
        'name' => 'tab',
        'items' => [
            ['url' => 'order/sampleApply/detail', 'name' => '样品申请明细', 'value' => 'detail'],
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
    
    public function datas()
    {
        return $this->hasMany('Gdoo\Order\Models\CustomerOrderData');
    }
}
