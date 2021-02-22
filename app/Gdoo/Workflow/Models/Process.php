<?php namespace Gdoo\Workflow\Models;

use Gdoo\Index\Models\BaseModel;

class Process extends BaseModel
{
    protected $table = 'work_process';

    public function user()
    {
        return $this->belongsTo('Gdoo\User\Models\User');
    }
}
