<?php namespace Gdoo\CustomerCost\Models;

use Gdoo\Index\Models\BaseModel;

class CostData extends BaseModel
{
    protected $table = 'customer_cost_data';

    public function customer()
    {
        return $this->belongsTo('Gdoo\Customer\Customer');
    }
}
