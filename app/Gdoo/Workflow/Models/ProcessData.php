<?php namespace Gdoo\Workflow\Models;

use Gdoo\Index\Models\BaseModel;

class ProcessData extends BaseModel
{
    protected $table = 'work_process_data';

    public function user()
    {
        return $this->belongsTo('Gdoo\User\Models\User');
    }
}
