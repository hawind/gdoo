<?php namespace Gdoo\Project\Models;

use Gdoo\Index\Models\BaseModel;

class Task extends BaseModel
{
    protected $table = 'project_task';

    public function project()
    {
        return $this->belongsTo('Gdoo\Project\Models\Project');
    }

    public function users()
    {
        return $this->belongsToMany('Gdoo\User\Models\User', 'project_task_user', 'task_id', 'user_id');
    }

    public function syncUsers($gets)
    {
        $users = $gets[$gets['type'].'_users'];
        $users = $users == '' ? [] : explode(',', $users);
        $this->users()->sync($users);
    }
}
