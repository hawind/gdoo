<?php namespace Gdoo\Model\Models;

use Gdoo\Index\Models\BaseModel;

class Model extends BaseModel
{
    protected $table = 'model';

    public function fields()
    {
        return $this->hasMany('Gdoo\Model\Models\Field');
    }

    public function children()
    {
        return $this->hasMany('Gdoo\Model\Models\Model', 'parent_id');
    }

}
