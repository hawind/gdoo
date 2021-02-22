<?php namespace Gdoo\Promotion\Models;

use Gdoo\Index\Models\BaseModel;

class PromotionMaterial extends BaseModel
{
    protected $table = 'promotion_material';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'promotion', 'url' => 'promotion/material/index', 'name' => '资料列表'],
        ]
    ];

    public static $bys = [
        'name'  => 'by',
        'items' => [
            ['value' => '-1', 'name' => '全部'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];

    public function promotion()
    {
        return $this->belongsTo('Gdoo\Promotion\Models\Promotion');
    }

    public function contact()
    {
        return $this->belongsTo('Gdoo\Customer\Models\Contact');
    }
}
