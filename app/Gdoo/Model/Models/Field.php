<?php namespace Gdoo\Model\Models;

use Gdoo\Index\Models\BaseModel;

class Field extends BaseModel
{
    protected $table = 'model_field';

    public function model()
    {
        return $this->belongsTo(Model::class);
    }
}