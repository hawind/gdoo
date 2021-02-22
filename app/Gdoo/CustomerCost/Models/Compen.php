<?php namespace Gdoo\CustomerCost\Models;

class Compen extends Cost
{
    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'compen.index', 'url' => 'customerCost/compen/index', 'name' => '合同补损'],
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
