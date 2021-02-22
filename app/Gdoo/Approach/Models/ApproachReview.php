<?php namespace Gdoo\Approach\Models;

use Gdoo\Index\Models\BaseModel;

class ApproachReview extends BaseModel
{
    protected $table = 'approach_review';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'review', 'url' => 'approach/review/index', 'name' => '进店核销'],
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
