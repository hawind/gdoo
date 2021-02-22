<?php namespace Gdoo\User\Models;

use Gdoo\Index\Models\BaseModel;

class Department extends BaseModel
{
    protected $table = 'department';

    static public $bys = [
        'name' => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)
        ->pluck('name', 'id');
    }
}
