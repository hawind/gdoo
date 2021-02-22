<?php namespace Gdoo\Model\Models;

use Gdoo\Index\Models\BaseModel;

class Run extends BaseModel
{
    protected $table = 'model_run';

    public function model()
    {
        return $this->belongsTo('Gdoo\Model\Models\Model');
    }
}
