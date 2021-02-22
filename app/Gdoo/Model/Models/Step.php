<?php namespace Gdoo\Model\Models;

use Gdoo\Index\Models\BaseModel;

class Step extends BaseModel
{
    protected $table = 'model_step';

    public function model()
    {
        return $this->belongsTo('Gdoo\Model\Models\Model');
    }
}
