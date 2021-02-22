<?php namespace Gdoo\User\Models;

use Gdoo\Index\Models\BaseModel;

class Message extends BaseModel
{
    protected $table = 'user_message';

    static public $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'message', 'url' => 'user/message/index', 'name' => '通知提醒'],
        ]
    ];

    static public $bys = [
        'name'  => 'status',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'unread', 'name' => '未读'],
            ['value' => 'read', 'name' => '已读'],
        ]
    ];
}
