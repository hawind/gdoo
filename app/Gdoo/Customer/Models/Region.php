<?php namespace Gdoo\Customer\Models;

use Gdoo\Index\Models\BaseModel;

class Region extends BaseModel
{
    protected $table = 'customer_region';

    /**
     * 设置字段黑名单
     */
    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo('Gdoo\Customer\Region');
    }

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)
        ->pluck('name', 'id');
    }
}
