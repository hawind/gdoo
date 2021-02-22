<?php namespace Gdoo\Promotion\Models;

use Gdoo\Index\Models\BaseModel;

class PromotionReview extends BaseModel
{
    protected $table = 'promotion_review';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'review', 'url' => 'promotion/review/index', 'name' => '促销核销'],
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
}
