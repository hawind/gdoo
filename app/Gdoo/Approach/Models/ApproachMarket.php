<?php namespace Gdoo\Approach\Models;

use Gdoo\Index\Models\BaseModel;

class ApproachMarket extends BaseModel
{
    protected $table = 'approach_market';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'market', 'url' => 'approach/market/index', 'name' => '超市列表'],
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
}
