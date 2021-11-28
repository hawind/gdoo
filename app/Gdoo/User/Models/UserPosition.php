<?php namespace Gdoo\User\Models;

use Gdoo\Index\Models\BaseModel;

class UserPosition extends BaseModel
{
    protected $table = 'user_post';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'position.index', 'url' => 'user/position/index', 'name' => '职位列表'],
        ]
    ];

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)
        ->pluck('name', 'id');
    }
}
