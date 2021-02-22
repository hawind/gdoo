<?php namespace Gdoo\User\Models;

use Gdoo\Index\Models\BaseModel;

class UserGroup extends BaseModel
{
    protected $table = 'user_group';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'group.index', 'url' => 'user/group/index', 'name' => '用户组列表'],
        ]
    ];
}
