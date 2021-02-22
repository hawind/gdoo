<?php namespace Gdoo\File\Models;

use Gdoo\Index\Models\BaseModel;

class Certificate extends BaseModel
{
    public $table = 'file_certificate';

    static public $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'user', 'url' => 'file/certificate/index', 'name' => '证照列表'],
        ]
    ];

    static public $bys = [
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
