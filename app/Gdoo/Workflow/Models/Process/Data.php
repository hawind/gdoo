<?php namespace Gdoo\Workflow\Models\Process;

use Gdoo\index\Models\BaseModel;

class Data extends BaseModel
{
    protected $table = 'work_process_data';

    public function user()
    {
        return $this->belongsTo('Gdoo\User\Models\User');
    }
}
