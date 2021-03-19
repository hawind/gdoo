<?php namespace Gdoo\User\Models;

use Gdoo\Index\Models\BaseModel;

class Role extends BaseModel
{
    protected $table = 'role';

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)
        ->pluck('name', 'id');
    }
}
