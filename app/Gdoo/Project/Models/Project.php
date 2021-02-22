<?php namespace Gdoo\Project\Models;

use Gdoo\Index\Models\BaseModel;

class Project extends BaseModel
{
    protected $table = 'project';

    public static $tabs = [
        ['id' => 0, 'name' => '进行中', 'color' => 'info'],
        ['id' => 1, 'name' => '已结束', 'color' => 'success'],
    ];

    public function tasks()
    {
        return $this->hasMany('Gdoo\Project\Models\Task');
    }
}
